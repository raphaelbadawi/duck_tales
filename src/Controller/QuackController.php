<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Duck;
use App\Entity\Quack;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class QuackController extends AbstractController
{
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
    private function updateQuackFields(ValidatorInterface $validator, $quack, String $content, Duck $duck): Quack
    {
        $quack->setContent($this->addUrlTagToContent($content));
        $quack->setCreatedAt(new DateTimeImmutable());
        $quack->setDuck($duck);

        // if needed we'll add some #[Assert()] annotations in the entity
        $errors = $validator->validate($quack);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
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

    private function parseUrlThumbnail(String $url)
    {
        // parses the url
        $url = htmlspecialchars(trim($url));
        $urlData = parse_url($url);
        $host = $urlData['host'];
        $file = fopen($url, 'r');

        // adds it to a string content
        $content = '';
        while (!feof($file)) {
            $content .= fgets($file, 1024);
        }

        // get meta tags as an entrypoint to relevant informations
        $meta_tags = get_meta_tags($url);

        // gets the title
        $title = '';

        if (array_key_exists('og:title', $meta_tags)) {
            $title = $meta_tags['og:title'];
        } else if (array_key_exists('twitter:title', $meta_tags)) {
            $title = $meta_tags['twitter:title'];
        } else {
            $title_pattern = '/<title>(.+)<\/title>/i';
            preg_match_all($title_pattern, $content, $title);

            if (!is_array($title[1])) {
                $title = $title[1];
            } else {
                if (count($title[1]) > 0) {
                    $title = $title[1][0];
                } else {
                    $title = 'Title not found!';
                }
            }
        }

        $title = ucfirst($title);
    }

    private function addUrlTagToContent(String $content): array|String|null // I was wrong this is even worse
    {
        $pattern =  "/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()!@:%_\+.~#?&\/\/=]*)/i";
        $replacement = '<a href="$0">$0</a>';
        $content = preg_replace($pattern, $replacement, $content);
        return $content;
    }

    #[Route('/quacks', name: 'quacks')]
    public function index(EntityManagerInterface $entityManager, MarkdownParserInterface $markdownParser): Response
    {
        $quacks = $this->fetchQuacks($entityManager);
        $quacks = array_map(fn ($quack) => $quack->setContent($markdownParser->transformMarkDown($quack->getContent())), $quacks);

        return $this->render('quack/index.html.twig', [
            // user will be important to display things accordingly to roles and id
            'user' => $this->getUser(),
            'quacks' => $quacks,
            'operation' => 'home'
        ]);
    }

    #[Route('/quacks/add', name: 'create_quack')]
    public function create(EntityManagerInterface $entityManager, ValidatorInterface $validator, SluggerInterface $slugger, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($request->getMethod() !== 'POST') {
            return $this->render('quack/create.html.twig', [
                'operation' => 'create'
            ]);
        }

        $quack = new Quack();
        $duck = $entityManager->getRepository(Duck::class)->findOneBy(['id' => $this->getUser()->getId()]);
        $quack = $this->updateQuackFields($validator, $quack, $request->get('content'), $duck);
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
    public function edit(EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request, Quack $quack): Response
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

        $quack = $this->updateQuackFields($validator, $quack, $request->get('content'), $quack->getDuck());

        $entityManager->persist($quack);

        // insert happens only at flush
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

        $entityManager->remove($quack);
        $entityManager->flush();

        return $this->redirectToRoute('quacks');
    }
}
