<?php

namespace App\Controller\Admin;

use App\Entity\Quack;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuackCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quack::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
