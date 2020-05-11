<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Ramsey\Uuid\Uuid;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(null)
            ->setEntityLabelInSingular('menu.category')
            ->setEntityLabelInPlural('menu.categories')
            ->setPageTitle(Crud::PAGE_INDEX, 'index.categories')
            ->setPageTitle(Crud::PAGE_NEW, 'new.categories')
            ->setPageTitle(Crud::PAGE_EDIT, 'edit.categories')
            ->setDefaultSort(['name' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): array
    {
        return [
            TextField::new('id')->setLabel('field.id')->onlyOnDetail(),
            TextField::new('name')->setLabel('field.name'),
            AssociationField::new('links')
                ->setLabel('field.links')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('name');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                static fn (Action $action): Action => $action->setLabel('action.add.category')
            );
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Category $category */
        $category = $entityInstance;
        $category->setId(Uuid::uuid4()->toString());

        parent::persistEntity($entityManager, $category);
    }
}
