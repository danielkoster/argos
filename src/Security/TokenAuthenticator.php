<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
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
	 * Symfony's rate limiter.
	 * @var RateLimiterFactory
	 */
	private RateLimiterFactory $apiInvalidRequestsLimiter;

	/**
	 * Create an authenticator.
	 * @param string $apiToken
	 * @param RateLimiterFactory $apiInvalidRequestsLimiter
	 */
	public function __construct(string $apiToken, RateLimiterFactory $apiInvalidRequestsLimiter) {
		if (empty($apiToken)) {
			throw new \InvalidArgumentException('API_TOKEN may not be empty');
		}

		$this->apiToken = $apiToken;
		$this->apiInvalidRequestsLimiter = $apiInvalidRequestsLimiter;
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
		if ($response = $this->consumeRateLimiter($request)) {
			return $response;
		}

		return new JsonResponse(
			['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
			Response::HTTP_UNAUTHORIZED
		);
	}

	/**
	 * @inheritDoc
	 */
	public function start(Request $request, AuthenticationException $authException = null): Response {
		if ($response = $this->consumeRateLimiter($request)) {
			return $response;
		}

		return new JsonResponse(['message' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
	}

	/**
	 * @inheritDoc
	 */
	public function supportsRememberMe(): bool {
		return false;
	}

	/**
	 * Consume the rate limiter, returns a response if hit.
	 * @param Request $request
	 * @return Response|null
	 */
	private function consumeRateLimiter(Request $request): ?Response {
		$limiter = $this->apiInvalidRequestsLimiter->create($request->getClientIp());
		$limit = $limiter->consume();

		if (false === $limit->isAccepted()) {
			return new Response(
				null,
				Response::HTTP_TOO_MANY_REQUESTS,
				['X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp()]
			);
		}

		return null;
	}
}
