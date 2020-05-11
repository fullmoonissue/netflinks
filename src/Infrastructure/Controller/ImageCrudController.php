<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Entity\Image;
use App\Domain\Entity\Recipient;
use App\Domain\Image\UnregisteredImageExtractor;
use App\Domain\Path\PublicImagePathGenerator;
use App\Infrastructure\Repository\ImageRepository;
use App\Infrastructure\Repository\RecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImageCrudController extends AbstractCrudController
{
    use RedirectionTrait;
    use ActionModalTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ImageRepository $imageRepository,
        private readonly RequestStack $requestStack,
        private readonly UnregisteredImageExtractor $unregisteredImageExtractor,
        private readonly PublicImagePathGenerator $publicImagesPathGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $pageUsedByEveryone = $this->requestStack->getCurrentRequest()->query->has('used_by_everyone');

        return $crud
            ->setSearchFields(null)
            ->setEntityLabelInSingular('menu.image')
            ->setEntityLabelInPlural('menu.images')
            ->setPageTitle(Crud::PAGE_INDEX, $pageUsedByEveryone ? 'index.images_deletable' : 'index.images')
            ->setPageTitle(Crud::PAGE_NEW, 'new.images')
            ->setPageTitle(Crud::PAGE_EDIT, 'edit.images')
            ->setDefaultSort(['filename' => 'ASC'])
            ->setPaginatorPageSize(25);
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): array
    {
        return [
            ImageField::new('filename')
                ->setLabel('field.image')
                ->setBasePath(PublicImagePathGenerator::PUBLIC_IMAGE_RELATIVE_FOLDER_PATH)
                ->hideOnForm(),
            TextField::new('filename')
                ->setLabel('field.filename')
                ->hideOnForm(),
            AssociationField::new('recipients')
                ->setLabel('field.recipients')
                ->setTextAlign('center')
                ->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (RecipientRepository $recipientRepository) {
                        return $recipientRepository->orderedForAssociationField();
                    },
                ]),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $ingestNewImagesAction = Action::new('ingest_new_images')
            ->setLabel('action.ingest_new_images')
            ->linkToCrudAction('ingestNewImages')
            ->createAsGlobalAction()
            ->displayIf(
                function () {
                    return [] !== $this->unregisteredImageExtractor->extract();
                }
            );

        $this->addModalActionShowRecipients($actions);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $ingestNewImagesAction)
            ->disable(Action::NEW);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                // If this field is added by the conventional way (->add('filename')),
                // the translation is not done when the filter modal is shown
                TextFilter::new('filename')
                    ->setLabel('field.filename')
            )
            ->add('recipients');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $parentIndexQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryParamsBag = $this->requestStack->getCurrentRequest()->query;
        if ($queryParamsBag->has('used_by_everyone')) {
            $ids = $this->imageRepository->findIdsUsedByEveryone();

            if ([] !== $ids) {
                $parentIndexQueryBuilder->andWhere(
                    $parentIndexQueryBuilder->expr()->in('entity.id', $ids)
                );
            }
        } elseif ($queryParamsBag->has('filters')) {
            $filters = $queryParamsBag->all('filters');

            if (isset($filters['recipients'])) {
                $this->imageRepository->recipientQueryBuilderIfNotThatOne(
                    $parentIndexQueryBuilder,
                    $filters['recipients']
                );
            }
        }

        return $parentIndexQueryBuilder;
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Image $image */
        $image = $entityInstance;
        $image->setId(Uuid::uuid4()->toString());

        parent::persistEntity($entityManager, $image);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Image $image */
        $image = $entityInstance;

        // In addition to the db record, the physical image is removed too
        $fs = new Filesystem();
        $fs->remove($this->publicImagesPathGenerator->generateAbsoluteFilePath($image));

        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function ingestNewImages(): RedirectResponse
    {
        $imagesNotSaved = $this->unregisteredImageExtractor->extract();

        foreach ($imagesNotSaved as $filename) {
            $this->imageRepository->add(
                (new Image())
                    ->setId(Uuid::uuid4()->toString())
                    ->setFilename($filename),
                true
            );
        }

        $this->addFlash(
            'success',
            $this->translator->trans(
                'success.new_images_ingested',
                [
                    '%count%' => count($imagesNotSaved),
                ]
            )
        );

        return $this->redirectToIndex();
    }

    private function addModalActionShowRecipients(Actions $actions): void
    {
        $entityId = $this->requestStack->getCurrentRequest()->query->get('entityId');
        if (null !== $entityId) {
            $image = $this->imageRepository->find($entityId);
            if (0 < $image->getRecipients()->count()) {
                $attachedRecipients = array_map(
                    function (Recipient $recipient) {
                        return $recipient->getName();
                    },
                    $image->getRecipients()->toArray()
                );

                $showRecipientsAction = Action::new('show_recipients')
                    ->setLabel('action.show_recipients');

                $this->actionAsModal($showRecipientsAction, implode('\n', $attachedRecipients));

                $actions->add(Crud::PAGE_DETAIL, $showRecipientsAction);
            }
        }
    }
}
