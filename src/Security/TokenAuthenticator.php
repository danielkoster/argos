<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Token authenticator.
 */
class TokenAuthenticator extends AbstractGuardAuthenticator {
	/**
	 * API token.
	 * @var string
	 */
	private string $apiToken;

	/**
	 * Create an authenticator.
	 * @param string $apiToken
	 */
	public function __construct(string $apiToken) {
		if (empty($apiToken)) {
			throw new \InvalidArgumentException('API_TOKEN may not be empty');
		}

		$this->apiToken = $apiToken;
	}

	/**
	 * @inheritDoc
	 */
	public function supports(Request $request): bool {
		return $request->headers->has('X-AUTH-TOKEN');
	}

	/**
	 * @inheritDoc
	 */
	public function getCredentials(Request $request) {
		return $request->headers->get('X-AUTH-TOKEN');
	}

	/**
	 * @inheritDoc
	 */
	public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface {
		if (null === $credentials) {
			// Authentication fails with HTTP 401 "Unauthorized".
			return null;
		}

		return new User('API user', bin2hex(random_bytes(16)), ['ROLE_USER']);
	}

	/**
	 * @inheritDoc
	 */
	public function checkCredentials($credentials, UserInterface $user): bool {
		return $credentials === $this->apiToken;
	}

	/**
	 * @inheritDoc
	 */
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		$data = [
			'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
		];

		return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
	}

	/**
	 * @inheritDoc
	 */
	public function start(Request $request, AuthenticationException $authException = null): Response {
		$data = [
			'message' => 'Authentication Required',
		];

		return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
	}

	/**
	 * @inheritDoc
	 */
	public function supportsRememberMe(): bool {
		return false;
	}
}
