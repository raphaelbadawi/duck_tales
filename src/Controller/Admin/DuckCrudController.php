<?php

namespace App\Controller\Admin;

use App\Entity\Duck;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DuckCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Duck::class;
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
