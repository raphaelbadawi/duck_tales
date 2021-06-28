<?php

namespace App\Controller;

use App\Entity\Duck;
use App\Entity\Quack;
use DateTimeImmutable;
use App\Service\UrlHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/quacks/{id}/comment', name: 'add_comment')]
    public function create(EntityManagerInterface $entityManager, ValidatorInterface $validator, UrlHelper $urlHelper, Request $request, Quack $quack): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($request->getMethod() !== 'POST') {
            return $this->redirectToRoute('quacks');
        }

        // set comment
        $comment = new Quack();
        $comment->setContent($urlHelper->addUrlTagToContent($request->get('comment')));
        $comment->setCreatedAt(new DateTimeImmutable());

        $duck = $entityManager->getRepository(Duck::class)->findOneBy(['id' => $this->getUser()->getId()]);
        $comment->setDuck($duck);
        $comment->setIsOld(false);

        // validate comment
        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            $session = $this->requestStack->getSession();
            $session->set('errors', $errors);
            $this->redirectToRoute('quacks');
        }

        // set parent/child relationship with post
        $quack->addComment($comment);

        // persist all the stuff
        $entityManager->persist($comment);
        $entityManager->persist($quack);
        $entityManager->flush();

        return $this->redirectToRoute('quacks');
    }
}
