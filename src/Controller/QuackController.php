<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Duck;
use App\Entity\Quack;
use DateTimeImmutable;
use App\Service\UrlHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class QuackController extends AbstractController
{
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    // abstraction to avoid code duplication
    private function fetchQuacks(EntityManagerInterface $entityManager): array
    {
        $quacks = $entityManager->getRepository(Quack::class)->findAll();

        if (!$quacks) {
            $quacks = [];
        }

        return $quacks;
    }

    // abstraction to avoid code duplication
    private function updateQuackFields(ValidatorInterface $validator, UrlHelper $urlHelper, $quack, String $content, Duck $duck, Int $previousId = 0): Quack|Response
    {
        $quack->setContent($urlHelper->addUrlTagToContent($content));
        $quack->setCreatedAt(new DateTimeImmutable());
        $quack->setDuck($duck);
        if (null === $quack->getOldId() && $previousId > 0) {
            $quack->setOldId($previousId);
        }
        $quack->setIsOld(false);

        // if needed we'll add some #[Assert()] annotations in the entity
        $errors = $validator->validate($quack);
        if (count($errors) > 0) {
            $session = $this->requestStack->getSession();
            $session->set('errors', $errors);
            $this->redirectToRoute('quacks');
        }

        return $quack;
    }

    private function handleFileUpload(Request $request, SluggerInterface $slugger): bool|Response|String // worst union type ever
    {
        $quackPicture = $request->files->get('picture');
        if ($quackPicture) {
            $originalFilename = pathinfo($quackPicture->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $quackPicture->guessExtension();

            // move the file to the directory where brochures are stored
            try {
                $quackPicture->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );
                return $newFilename;
            } catch (FileException $e) {
                return $this->redirectToRoute('quacks');
            }
        }
        return false;
    }

    private function handleTags(String $tags): array
    {
        $tagsArray = explode(',', str_replace(' ', '', $tags));
        $tagsArray = array_map(fn ($content) => (new Tag())->setContent($content), $tagsArray);
        return $tagsArray;
    }

    #[Route('/quacks', name: 'quacks')]
    public function index(EntityManagerInterface $entityManager, MarkdownParserInterface $markdownParser): Response
    {
        $quacks = $this->fetchQuacks($entityManager);
        $quacks = array_map(fn ($quack) => $quack->setContent($markdownParser->transformMarkDown($quack->getContent())), $quacks);

        // if errors have been set before a hard redirect to quacks (which is home for now)
        $session = $this->requestStack->getSession();
        if (null !== $session->get('errors') && !empty($session->get('errors'))) {
            $errors = $session->get('errors');
            $session->set('errors', null);
        }

        return $this->render('quack/index.html.twig', [
            // user will be important to display things accordingly to roles and id
            'user' => $this->getUser(),
            'quacks' => $quacks,
            'operation' => 'home',
            'errors' => isset($errors) ? $errors : null
        ]);
    }

    #[Route('/quacks/{id}/diffs', name: 'diffs_quack')]
    public function showDiffs(EntityManagerInterface $entityManager,  MarkdownParserInterface $markdownParser, Quack $quack): Response
    {
        // fetch previous iterations of the same post
        if (null !== $quack->getOldId()) {
            $oldQuacks = $entityManager->getRepository(Quack::class)->findBy(['oldId' => $quack->getOldId()]);
            $originalQuack = $entityManager->getRepository(Quack::class)->findOneBy(['id' => $quack->getOldId()]);
            $theWholeQuackHistory = [$originalQuack, ...$oldQuacks];
            $theWholeQuackHistory = array_map(fn ($quack) => $quack->setContent($markdownParser->transformMarkDown($quack->getContent())), $theWholeQuackHistory);
        } else {
            $theWholeQuackHistory = [];
        }

        $quack->setContent($markdownParser->transformMarkDown($quack->getContent()));

        return $this->render('quack/single/diffs.html.twig', [
            'quack' => $quack,
            'quacks' => $theWholeQuackHistory,
            'operation' => 'diffs'
        ]);
    }

    #[Route('/quacks/add', name: 'create_quack')]
    public function create(EntityManagerInterface $entityManager, ValidatorInterface $validator, SluggerInterface $slugger, UrlHelper $urlHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($request->getMethod() !== 'POST') {
            return $this->render('quack/create.html.twig', [
                'operation' => 'create'
            ]);
        }

        $quack = new Quack();
        $duck = $entityManager->getRepository(Duck::class)->findOneBy(['id' => $this->getUser()->getId()]);
        $quack = $this->updateQuackFields($validator, $urlHelper, $quack, $request->get('content'), $duck);
        $newFileName = $this->handleFileUpload($request, $slugger);
        $tags = $this->handleTags($request->get('tags'));

        if ($newFileName) {
            $quack->setPicture($newFileName);
        }
        foreach ($tags as $tag) {
            $quack->addTag($tag);
        }

        $entityManager->persist($quack);

        // insert happens only at flush
        $entityManager->flush();

        return $this->redirectToRoute('quacks');
    }

    #[Route('quacks/{id}/edit', name: 'edit_quack')]
    // automatic instantiation with the id route parameter, avoiding the getRepository->find thing
    public function edit(EntityManagerInterface $entityManager, ValidatorInterface $validator, SluggerInterface $slugger, UrlHelper $urlHelper, Request $request, Quack $quack): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($this->getUser()->getId() !== $quack->getDuck()->getId()) {
            return $this->redirectToRoute('quacks');
        }

        if ($request->getMethod() !== 'POST') {
            $tags = array_map(fn ($duck) => $duck->getContent(), $quack->getTags()->toArray());
            return $this->render('quack/create.html.twig', [
                'tags' => $tags,
                'quack' => $quack,
                'operation' => 'edit'
            ]);
        }

        $quack->setIsOld(true);
        $entityManager->persist($quack);
        $entityManager->flush();

        $duck = $quack->getDuck();
        $previousId = null !== $quack->getOldId() ? $quack->getOldId() : $quack->getId();
        $quack = new Quack();
        $quack = $this->updateQuackFields($validator, $urlHelper, $quack, $request->get('content'), $duck, $previousId);

        $newFileName = $this->handleFileUpload($request, $slugger);
        $tags = $this->handleTags($request->get('tags'));

        if ($newFileName) {
            $quack->setPicture($newFileName);
        }
        foreach ($tags as $tag) {
            $quack->addTag($tag);
        }

        $entityManager->persist($quack);
        $entityManager->flush();

        return $this->redirectToRoute('quacks');
    }

    #[Route('quacks/{id}/delete', name: 'delete_quack')]
    // automatic instantiation with the id route parameter, avoiding the getRepository->find thing
    public function destroy(EntityManagerInterface $entityManager, Quack $quack): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($this->getUser()->getId() !== $quack->getDuck()->getId()) {
            return $this->redirectToRoute('quacks');
        }

        // fetch previous iterations of the same post
        if (null !== $quack->getOldId()) {
            $oldQuacks = $entityManager->getRepository(Quack::class)->findBy(['oldId' => $quack->getOldId()]);
            $originalQuack = $entityManager->getRepository(Quack::class)->findOneBy(['id' => $quack->getOldId()]);
        }

        // delete all iterations of the same post
        $entityManager->remove($quack);
        foreach ($oldQuacks as $oldQuack) {
            $entityManager->remove($oldQuack);
        }
        $entityManager->remove($originalQuack);
        $entityManager->flush();

        return $this->redirectToRoute('quacks');
    }
}
