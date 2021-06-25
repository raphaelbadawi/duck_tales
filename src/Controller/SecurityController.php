<?php

namespace App\Controller;

use App\Entity\Duck;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/connect/google', name: "connect_google_start")]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google_main') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'public_profile', 'email' // the scopes you want to access
            ], []);
    }

    /**
     * @Route("/connect/google/check", name="connect_google_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        // intentionnaly left blank to let the GoogleAuthenticator kick in
    }

    #[Route('/profile', name: 'profile')]
    public function profile(EntityManagerInterface $entityManager, ValidatorInterface $validator, UserPasswordHasherInterface $passwordEncoder, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->getMethod() === 'POST') {
            $user = $this->getUser();
            $duck = $entityManager->getRepository(Duck::class)->findOneBy(['id' => $user->getId()]);
            $duck->setFirstname(!empty($request->get('first_name')) ? $request->get('first_name') : $duck->getFirstname());
            $duck->setLastName(!empty($request->get('last_name')) ? $request->get('last_name') : $duck->getLastName());
            $duck->setDuckName(!empty($request->get('duck_name')) ? $request->get('duck_name') : $duck->getDuckName());
            $duck->setEmail(!empty($request->get('email')) ? $request->get('email') : $duck->getEmail());
            $duck->setPassword(!empty($request->get('password')) ? $passwordEncoder->hashPassword(
                $duck,
                $request->get('password')
            ) : $duck->getPassword());

            $errors = $validator->validate($duck);
            if (count($errors) > 0) {
                return $this->render('security/profile.html.twig', ['operation' => 'profile', 'errors' => $errors]);
            }

            $entityManager->persist($duck);
            $entityManager->flush();
            return $this->redirectToRoute('quacks');
        }
        return $this->render('security/profile.html.twig', ['operation' => 'profile', 'user' => $this->getUser()]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(EntityManagerInterface $entityManager, ValidatorInterface $validator, UserPasswordHasherInterface $passwordEncoder, Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            $duck = new Duck();
            $duck->setFirstname($request->get('first_name'));
            $duck->setLastname($request->get('last_name'));
            $duck->setDuckname($request->get('duck_name'));
            $duck->setEmail($request->get('email'));
            $duck->setPassword(
                $passwordEncoder->hashPassword(
                    $duck,
                    $request->get('password')
                )
            );

            $errors = $validator->validate($duck);

            if (count($errors) > 0) {
                return $this->render('security/register.html.twig', ['operation' => 'register', 'errors' => $errors]);
            }

            $entityManager->persist($duck);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', ['operation' => 'register']);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('quacks');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'operation' => 'login']);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        return $this->redirectToRoute('quacks');
    }
}
