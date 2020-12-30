<?php

namespace App\Command;

use App\Message\ParseFeedMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Command to parse feed.
 */
class ParseFeedCommand extends Command {
	/**
	 * @inheritDoc
	 */
	protected static $defaultName = 'app:parse-feed';

	/**
	 * Symfony's message bus.
	 * @var MessageBusInterface
	 */
	private MessageBusInterface $bus;

	/**
	 * Create a command.
	 * @param MessageBusInterface $bus
	 */
	public function __construct(MessageBusInterface $bus) {
		parent::__construct(self::$defaultName);
		$this->bus = $bus;
	}

	/**
	 * @inheritDoc
	 */
	protected function configure() {
		$this->setDescription('Dispatches a message to parse the feed and store relevant episodes');
	}

	/**
	 * @inheritDoc
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->bus->dispatch(new ParseFeedMessage());
		$output->write('Dispatched message to parse feed and store relevant episodes');

		return Command::SUCCESS;
	}
}
