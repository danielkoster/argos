<?php

namespace App\EventListener;

use Sentry\Event;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Listener which is called before Sentry events are send.
 */
class SentryBeforeSendListener {
	/**
	 * Array of unsupported exception types.
	 * @var string[]
	 */
	private const UNSUPPORTED_TYPES = [
		AccessDeniedHttpException::class,
		CommandNotFoundException::class,
		NotFoundHttpException::class,
		MethodNotAllowedHttpException::class,
		HandlerFailedException::class,
	];

	/**
	 * Handles {@see Event} before it is sent to Sentry. Certain exceptions aren't supported, these are filtered.
	 * @param Event $event The event.
	 * @return Event|null The event.
	 */
	public function __invoke(Event $event): ?Event {
		$exceptions = $event->getExceptions();
		$exception = reset($exceptions);
		if (false !== $exception && in_array($exception['type'], self::UNSUPPORTED_TYPES, true)) {
			return null;
		}

		return $event;
	}
}
