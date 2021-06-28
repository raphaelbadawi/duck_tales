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
    private function updateQuackFields(ValidatorInterface $validator, $quack, String $content, Duck $duck): Quack|Response
    {
        $quack->setContent($this->addUrlTagToContent($content));
        $quack->setCreatedAt(new DateTimeImmutable());
        $quack->setDuck($duck);

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

    private function parseUrlThumbnail(array $urlMatch)
    {
        // parses the url
        $url = htmlspecialchars(trim($urlMatch[0]));
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

        // get the description
        $desc = '';

        if (array_key_exists('description', $meta_tags)) {
            $desc = $meta_tags['description'];
        } else if (array_key_exists('og:description', $meta_tags)) {
            $desc = $meta_tags['og:description'];
        } else if (array_key_exists('twitter:description', $meta_tags)) {
            $desc = $meta_tags['twitter:description'];
        } else {
            $desc = 'Description not found!';
        }

        $desc = ucfirst($desc);

        // get the picture
        $img_url = '';

        if (array_key_exists('og:image', $meta_tags)) {
            $img_url = $meta_tags['og:image'];
        } else if (array_key_exists('og:image:src', $meta_tags)) {
            $img_url = $meta_tags['og:image:src'];
        } else if (array_key_exists('twitter:image', $meta_tags)) {
            $img_url = $meta_tags['twitter:image'];
        } else if (array_key_exists('twitter:image:src', $meta_tags)) {
            $img_url = $meta_tags['twitter:image:src'];
        } else {
            // image not found in meta tags so find it from content
            $img_pattern = '/<img[^>]*' . 'src=[\"|\'](.*)[\"|\']/Ui';
            $images = '';
            preg_match_all($img_pattern, $content, $images);

            $total_images = is_array($images[1]) ? count($images[1]) : 0;
            if ($total_images > 0) {
                $images = $images[1];
                for ($i = 0; $i < $total_images; $i++) {
                    if ($images[$i][0] == "/" ? getimagesize("https://" . $host . $images[$i]) : getimagesize($images[$i])) {
                        list($width, $height, $type, $attr) = $images[$i][0] == "/" ? getimagesize("https://" . $host . $images[$i]) : getimagesize($images[$i]);
                        if ($width > 100) { // we don't want a mere icon, so we filter by width
                            $img_url = $images[$i][0] == "/" ? getimagesize("https://" . $host . $images[$i]) : getimagesize($images[$i]);
                        }
                        break;
                    }
                }
            }
        }
        $urlTag = "<div class='border-solid border-black border-2 rounded-sm block w-full'><a href='$url'><div>$title</div>";
        $urlTag .= "<div ><img class='w-32 mx-auto my-2 rounded-md' src='$img_url' alt='Picture preview'></div>";
        $urlTag .= "<div class='text-sm'>$desc</div>";
        $urlTag .= "<div>$host</div></a></div>";
        return $urlTag;
    }

    private function addUrlTagToContent(String $content): array|String|null // I was wrong this is even worse
    {
        $pattern =  "/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()!@:%_\+.~#?&\/\/=]*)/i";
        // $replacement = '<a href="$0">$0</a>';
        $content = preg_replace_callback($pattern, [$this, "parseUrlThumbnail"], $content);
        return $content;
    }

    #[Route('/quacks', name: 'quacks')]
    public function index(EntityManagerInterface $entityManager, MarkdownParserInterface $markdownParser): Response
    {
        $quacks = $this->fetchQuacks($entityManager);
        $quacks = array_map(fn ($quack) => $quack->setContent($markdownParser->transformMarkDown($quack->getContent())), $quacks);

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
