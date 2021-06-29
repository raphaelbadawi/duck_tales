<?php

namespace App\Controller\Admin;

use App\Entity\Duck;
use App\Entity\Quack;
use App\Controller\Admin\QuackCrudController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
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
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Quack');
        yield MenuItem::linkToCrud('Quacks', 'fa fa-feather', Quack::class);
        yield MenuItem::section('Ducks');
        yield MenuItem::subMenu('Quack Me', 'fa fa-tooth')->setSubItems([
            MenuItem::linkToCrud('Ducks', 'fa fa-user', Duck::class)
        ]);

        MenuItem::linkToLogout('logout', 'fa fa-exit');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
