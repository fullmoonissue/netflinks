<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Entity\Recipient;
use App\Infrastructure\Repository\NewsletterRepository;
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

class RecipientCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Recipient::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(null)
            ->setEntityLabelInSingular('menu.recipient')
            ->setEntityLabelInPlural('menu.recipients')
            ->setPageTitle(Crud::PAGE_INDEX, 'index.recipients')
            ->setPageTitle(Crud::PAGE_NEW, 'new.recipients')
            ->setPageTitle(Crud::PAGE_EDIT, 'edit.recipients')
            ->setDefaultSort(['name' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): array
    {
        return [
            TextField::new('name')->setLabel('field.name'),
            TextField::new('short')->setLabel('field.short'),
            AssociationField::new('links')
                ->setLabel('field.links')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            AssociationField::new('images')
                ->setLabel('field.images')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('short');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                static fn (Action $action): Action => $action->setLabel('action.add.recipient')
            );
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Recipient $recipient */
        $recipient = $entityInstance;
        $recipient->setId(Uuid::uuid4()->toString());

        parent::persistEntity($entityManager, $recipient);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Recipient $recipient */
        $recipient = $entityInstance;

        $this->removeAssociatedNewsletter($recipient);

        parent::deleteEntity($entityManager, $entityInstance);
    }

    private function removeAssociatedNewsletter(Recipient $recipient): void
    {
        $newsletter = $this->newsletterRepository->findOneBy(['recipient' => $recipient]);
        if (null !== $newsletter) {
            $this->newsletterRepository->remove($newsletter);
        }
    }
}
