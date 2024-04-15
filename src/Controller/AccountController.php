<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\UserAccountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AccountController extends AbstractController
{
    #[Route('/profile/edit', name: 'profile_edit')]
    public function edit(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(UserAccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // Re-authenticate the user only if the password was changed
            if ($plainPassword) {
                // This will update the security token in the session
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token); // Use injected TokenStorageInterface
                $session->set('_security_main', serialize($token));

                // Migrate the session to prevent session fixation issues
                $session->migrate(true);
            }

            $this->addFlash('success', 'Your profile has been updated.');

            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
