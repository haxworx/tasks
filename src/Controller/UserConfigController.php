<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\UserConfig;
use App\Form\ApiTokenType;
use App\Form\UserConfigType;
use App\Utils\ApiToken;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserConfigController extends AbstractController
{
    #[Route('/user/config', name: 'user_config')]
    public function index(
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher,
        NotifierInterface $notifier,
        LoggerInterface $logger,
    ): Response {
        $user = $this->getUser();

        $config = new UserConfig();

        $form = $this->createForm(UserConfigType::class, $config);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$passwordHasher->isPasswordValid($user, $config->getOldPassword())) {
                $notifier->send(new Notification('Incorrect existing password', ['browser']));
            } else {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $config->getPlainPassword(),
                );

                $user->setPassword($hashedPassword);

                $logger->info('User changed password', [
                    'user' => $this->getUser()->getUserIdentifier(),
                    'route' => $request->attributes->get('_route'),
                ]);

                $em = $doctrine->getManager();
                $em->persist($user);
                $em->flush();
                $notifier->send(new Notification('Password updated', ['browser']));
            }
        }

        $tokenForm = $this->createForm(ApiTokenType::class);

        $tokenForm->handleRequest($request);

        if ($tokenForm->isSubmitted() && $tokenForm->isValid()) {
            $user->setApiToken(ApiToken::generate());
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $notifier->send(new Notification('API token updated', ['browser']));
        }

        return $this->render('user_config/index.html.twig', [
            'form' => $form,
            'token_form' => $tokenForm,
            'api_token' => $user->getApiToken(),
        ]);
    }
}
