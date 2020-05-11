<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Entity\Link;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\LinkRepository;
use App\Infrastructure\Repository\RecipientRepository;
use App\Infrastructure\Repository\TagRepository;
use DateTimeImmutable;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RequestStack;

final class LinkCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LinkRepository $linkRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Link::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(null)
            ->setEntityLabelInSingular('menu.link')
            ->setEntityLabelInPlural('menu.links')
            ->setPageTitle(Crud::PAGE_INDEX, 'index.links')
            ->setPageTitle(Crud::PAGE_NEW, 'new.links')
            ->setPageTitle(Crud::PAGE_EDIT, 'edit.links')
            ->setDefaultSort(['date' => 'DESC'])
            ->setPaginatorPageSize(25);
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): array
    {
        return [
            TextField::new('id')->setLabel('field.id')->onlyOnDetail(),
            TextField::new('description')->setLabel('field.description'),
            UrlField::new('url')->setLabel('field.url')->setFormTypeOption('default_protocol', 'https'),
            DateField::new('date')
                ->setLabel('field.date')
                ->setFormat('full')
                ->hideOnForm(),
            BooleanField::new('isFavorite')
                ->setLabel('field.is_favorite'),
            AssociationField::new('category')
                ->setLabel('field.category')
                ->setTextAlign('center')
                ->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (CategoryRepository $categoryRepository) {
                        return $categoryRepository->orderedForAssociationField();
                    },
                ]),
            AssociationField::new('recipients')
                ->setLabel('field.recipients')
                ->setTextAlign('center')
                ->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (RecipientRepository $recipientRepository) {
                        return $recipientRepository->orderedForAssociationField();
                    },
                ]),
            AssociationField::new('tags')
                ->setLabel('field.tags')
                ->setTextAlign('center')
                ->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (TagRepository $tagRepository) {
                        return $tagRepository->orderedForAssociationField();
                    },
                ]),
            TextareaField::new('note')->setLabel('field.note')->setRequired(false),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                static fn (Action $action): Action => $action->setLabel('action.add.link')
            );
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('url')
            ->add('description')
            ->add('date')
            ->add('isFavorite')
            ->add('category')
            ->add('tags')
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
        if ($queryParamsBag->has('filters')) {
            $filters = $queryParamsBag->all('filters');

            if (isset($filters['recipients'])) {
                $this->linkRepository->recipientQueryBuilderIfNotThatOne(
                    $parentIndexQueryBuilder,
                    $filters['recipients']
                );
            }
        }

        return $parentIndexQueryBuilder;
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Link $link */
        $link = $entityInstance;

        $link->setId(Uuid::uuid4()->toString());
        $link->setDate(new DateTimeImmutable());

        parent::persistEntity($entityManager, $link);
    }
}
