<?php

namespace App\Controller;

use App\Entity\Duck;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    #[Route('/notification/create', name: 'create_notification')]
    public function create(NotifierInterface $notifier, EntityManagerInterface $entityManager, Request $request): Response
    {
        // TODO: refactor to use Mercure chat instead of email
        $notification = (new Notification('Notification from Duck Tales'))
            ->content('You got some contributions to review. Please look at the Quack #' . $request->get('warned_id'))
            ->importance(Notification::IMPORTANCE_HIGH);


        $admins = [];
        $admins = array_map(fn ($duck) => $duck->hasRoleAdmin() ? $duck->getEmail() : NULL, $entityManager->getRepository(Duck::class)->findAll());

        // foreach ($admins as $admin) {
        //     $recipient = new Recipient(
        //         $admin
        //     );
        //     $notifier->send($notification, $recipient);
        // }
        $recipient = new Recipient(
            'badawiraphael@posteo.net'
        );
        $notifier->send($notification, $recipient);


        // Send the notification to the recipient


        return $this->redirectToRoute('quacks');
    }
}
