<?php

namespace App\Controller\Admin;

use App\Entity\Duck;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DuckCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Duck::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('firstname'),
            TextField::new('lastname'),
            TextField::new('duckname'),
            TextField::new('email'),
            ChoiceField::new('hasRoleAdmin')->setChoices(fn () => ['Yes' => 'Yes', 'No' => 'No']),
        ];
    }
}
