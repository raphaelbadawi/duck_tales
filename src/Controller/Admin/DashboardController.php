<?php

namespace App\Controller\Admin;

use App\Entity\Duck;
use App\Entity\Quack;
use App\Controller\Admin\QuackCrudController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(QuackCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Duck Tales');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Back to the mare', 'fa fa-water', '/quacks');
        yield MenuItem::section('Quack');
        yield MenuItem::linkToCrud('Quacks', 'fa fa-feather', Quack::class);
        yield MenuItem::section('Ducks');
        yield MenuItem::subMenu('Quack Me', 'fa fa-tooth')->setSubItems([
            MenuItem::linkToCrud('Ducks', 'fa fa-user', Duck::class)
        ]);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName('The unique ' . $user->getDuckName())
            // use this method if you don't want to display the user image
            ->displayUserAvatar(false)
            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToUrl('Back to the mare', 'fa fa-water', '/quacks')
            ]);
    }
}
