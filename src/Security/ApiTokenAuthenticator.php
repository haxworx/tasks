<?php

declare(strict_types=1);

// declare(strict_types=1);

namespace App\Security;

use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $header = $request->headers->get('Authorization');
        if (null === $header) {
            $this->logger->alert('Authentication', [
                'mesg' => 'Missing API token',
                'route' => $request->attributes->get('_route'),
                'ip' => $request->getClientIp(),
            ]);
            throw new CustomUserMessageAuthenticationException('No api token provided');
        }
        // Skip 'Bearer '
        $apiToken = substr($header, 7);

        return new SelfValidatingPassport(
            new UserBadge($apiToken, function ($apiToken) {
                $user = $this->userRepository->findOneByApiToken($apiToken);
                if (null === $user) {
                    $this->logger->alert('Authentication', [
                        'mesg' => sprintf('Failed authentication: %s', $apiToken),
                        'route' => $this->requestStack->getCurrentRequest()->attributes->get('_route'),
                        'ip' => $this->requestStack->getCurrentRequest()->getClientIp(),
                    ]);
                    throw new CustomUserMessageAuthenticationException('Authentication failed');
                }
                $user->setRoles(['ROLE_API']);

                return $user;
            }),
            []
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' > strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
