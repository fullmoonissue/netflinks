<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Entity\Newsletter;
use App\Domain\Newsletter\AreImagesAssignedToRecipient;
use App\Domain\Newsletter\AssignImagesToRecipient;
use App\Domain\Newsletter\AssignNextLinks;
use App\Domain\Newsletter\LastLinkGuesser;
use App\Domain\Newsletter\RequestedImagesCounter;
use App\Domain\Path\PublicPdfPathGenerator;
use App\Infrastructure\Pdf\Netflinks;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\ImageRepository;
use App\Infrastructure\Repository\NewsletterRepository;
use App\Infrastructure\Repository\RecipientRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NewsletterCrudController extends AbstractCrudController
{
    use RedirectionTrait;
    use ActionModalTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly NewsletterRepository $newsletterRepository,
        private readonly Netflinks $netflinks,
        private readonly RequestStack $requestStack,
        private readonly AreImagesAssignedToRecipient $areImagesAssignedToRecipient,
        private readonly RequestedImagesCounter $requestedImagesCounter,
        private readonly AssignImagesToRecipient $assignImagesToRecipient,
        private readonly PublicPdfPathGenerator $publicPdfsPathGenerator,
        private readonly LastLinkGuesser $lastLinkGuesser,
        private readonly AssignNextLinks $assignNextLinks,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Newsletter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(null)
            ->setEntityLabelInSingular('menu.newsletter')
            ->setEntityLabelInPlural('menu.newsletters')
            ->setPageTitle(Crud::PAGE_INDEX, 'index.newsletters')
            ->setPageTitle(Crud::PAGE_NEW, 'new.newsletters')
            ->setPageTitle(
                Crud::PAGE_EDIT,
                function (Newsletter $newsletter): string {
                    return $this->translator->trans(
                        'edit.newsletters',
                        [
                            '%recipient_name%' => $newsletter->getRecipient()->getName(),
                        ]
                    );
                }
            )
            ->setDefaultSort(['date' => 'DESC'])
            ->setPaginatorPageSize(25);
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): array
    {
        return [
            AssociationField::new('recipient')
                ->setLabel('field.recipient')
                ->setTextAlign('center')
                ->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (RecipientRepository $recipientRepository) {
                        return $recipientRepository->filteredAndOrderedForAssociationField();
                    },
                ])
                ->hideWhenUpdating(),
            DateField::new('date')
                ->setLabel('field.date')
                ->setFormat('full'),
            AssociationField::new('firstLink')
                ->setLabel('field.first_link')
                ->setTextAlign('center'),
            AssociationField::new('lastLink')
                ->setLabel('field.last_link')
                ->setRequired(false)
                ->setTextAlign('center')
                ->setHelp('help.last_link'),
            AssociationField::new('images')
                ->setLabel('field.images')
                ->setTextAlign('center')
                ->setFormTypeOptions([
                    'query_builder' => function (ImageRepository $imageRepository) {
                        return $imageRepository->orderedForAssociationField();
                    },
                ]),
            AssociationField::new('categories')
                ->setLabel('field.categories')
                ->setTextAlign('center')
                ->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (CategoryRepository $categoryRepository) {
                        return $categoryRepository->orderedForAssociationField();
                    },
                ]),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $assignImagesToRecipientActionName = 'assign_images_to_recipient';
        $assignImagesToRecipient = Action::new($assignImagesToRecipientActionName)
            ->setLabel('action.assign_images_to_recipient')
            ->linkToCrudAction('assignImagesToRecipient')
            ->displayIf(
                function (Newsletter $newsletter) {
                    return !$this->areImagesAssignedToRecipient->isSatisfiedBy($newsletter);
                }
            );

        $generatePdfActionName = 'generate_pdf';
        $generatePdf = Action::new($generatePdfActionName)
            ->setLabel('action.generate_pdf')
            ->linkToCrudAction('generatePdf');

        $prepareNextNLActionName = 'prepare_next_nl';
        $prepareNextNL = Action::new($prepareNextNLActionName)
            ->setLabel('action.prepare_next_nl')
            ->linkToCrudAction('prepareNextNL')
            ->setHtmlAttributes(
                [
                    'onclick' => sprintf(
                        'return confirm("%s");',
                        str_replace(
                            '"',
                            '\"',
                            $this->translator->trans('alert.actions_on_prepare_next_nl')
                        )
                    ),
                ]
            );

        $this->addModalActionComputeRequestedImageCount($actions);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $generatePdf)
            ->add(Crud::PAGE_INDEX, $assignImagesToRecipient)
            ->add(Crud::PAGE_INDEX, $prepareNextNL)
            ->add(Crud::PAGE_DETAIL, $generatePdf)
            ->add(Crud::PAGE_DETAIL, $assignImagesToRecipient)
            ->reorder(
                Crud::PAGE_INDEX,
                [
                    Action::DETAIL,
                    Action::EDIT,
                    Action::DELETE,
                    $assignImagesToRecipientActionName,
                    $generatePdfActionName,
                    $prepareNextNLActionName,
                ]
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                static fn (Action $action): Action => $action->setLabel('action.add.newsletter')
            );
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('date');
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Newsletter $newsletter */
        $newsletter = $entityInstance;
        $newsletter->setId(Uuid::uuid4()->toString());

        $this->assignLastLink($newsletter);

        parent::persistEntity($entityManager, $newsletter);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Newsletter $newsletter */
        $newsletter = $entityInstance;

        $this->assignLastLink($newsletter);

        parent::updateEntity($entityManager, $newsletter);
    }

    public function prepareNextNL(AdminContext $context): RedirectResponse
    {
        /** @var Newsletter $newsletter */
        $newsletter = $context->getEntity()->getInstance();

        try {
            $this->assignNextLinks->assign($newsletter);

            $newsletter->getImages()->clear();
            $this->newsletterRepository->update($newsletter);

            $this->addFlash(
                'success',
                $this->translator->trans('success.next_nl_prepared')
            );
        } catch (NoResultException) {
            $this->addFlash(
                'warning',
                $this->translator->trans('error.no_new_link')
            );
        }

        return $this->redirectToIndex();
    }

    public function assignImagesToRecipient(AdminContext $context): RedirectResponse
    {
        /** @var Newsletter $newsletter */
        $newsletter = $context->getEntity()->getInstance();

        $this->assignImagesToRecipient->assign($newsletter);

        $this->addFlash(
            'success',
            $this->translator->trans(
                'success.images_assigned_to',
                [
                    '%recipient%' => $newsletter->getRecipient()->getName(),
                ]
            )
        );

        return $this->redirectToIndex();
    }

    public function generatePdf(AdminContext $context): RedirectResponse
    {
        /** @var Newsletter $newsletter */
        $newsletter = $context->getEntity()->getInstance();

        if ($this->isNewsletterDateInThePast($newsletter->getDate())) {
            $this->addFlash(
                'warning',
                $this->translator->trans('error.nl_date_in_the_past')
            );

            return $this->redirectToIndex();
        }

        try {
            $this->netflinks->prepareDocument($newsletter);
            $pdfContent = $this->netflinks->downloadAsString();
        } catch (Exception $e) {
            $this->addFlash(
                'warning',
                $e->getMessage()
            );

            return $this->redirectToIndex();
        }

        $pdfAbsolutePath = $this->publicPdfsPathGenerator->generateAbsoluteFilePath($newsletter);

        file_put_contents($pdfAbsolutePath, $pdfContent);

        $this->addFlash(
            'success',
            $this->translator->trans(
                'success.pdf_generated',
                [
                    '%path%' => $pdfAbsolutePath,
                ]
            )
        );

        return $this->redirectToIndex();
    }

    private function addModalActionComputeRequestedImageCount(Actions $actions): void
    {
        $entityId = $this->requestStack->getCurrentRequest()->query->get('entityId');
        if (null !== $entityId) {
            $newsletter = $this->newsletterRepository->find($entityId);
            $requestedImageCount = $this->requestedImagesCounter->count($newsletter);

            $computeRequestedImageCountAction = Action::new('compute_requested_image_count')
                ->setLabel('action.compute_requested_image_count');

            $this->actionAsModal(
                $computeRequestedImageCountAction,
                $this->translator->trans(
                    'alert.image_requested_count',
                    [
                        '%count%' => $requestedImageCount,
                    ]
                )
            );

            $actions->add(Crud::PAGE_DETAIL, $computeRequestedImageCountAction);
        }
    }

    private function isNewsletterDateInThePast(DateTimeImmutable $nlDate): bool
    {
        return $nlDate <= new DateTimeImmutable('yesterday');
    }

    private function assignLastLink(Newsletter $newsletter): void
    {
        $lastLink = $this->lastLinkGuesser->guess($newsletter);
        $newsletter->setLastLink($lastLink);
    }
}
