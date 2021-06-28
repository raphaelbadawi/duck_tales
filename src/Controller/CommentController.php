<?php

namespace App\Controller;

use App\Entity\Quack;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/quacks/{id}/comment', name: 'add_comment')]
    public function create(EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request, Quack $quack): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($request->getMethod() !== 'POST') {
            return $this->redirectToRoute('quacks');
        }

        $comment = new Quack();
        $comment->setContent($request->get('comment'));
        $comment->setCreatedAt(new DateTimeImmutable());

        $quack->addComment($comment);
    }
}
