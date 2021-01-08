<?php

namespace App\Command;

use App\Message\ParseFeedMessage;
use App\Repository\FeedRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Command to parse feed.
 */
class ParseFeedsCommand extends Command {
	/**
	 * @inheritDoc
	 */
	protected static $defaultName = 'app:parse-feeds';

	/**
	 * Symfony's message bus.
	 * @var MessageBusInterface
	 */
	private MessageBusInterface $bus;

	/**
	 * The feed repository.
	 * @var FeedRepository
	 */
	private FeedRepository $feedRepository;

	/**
	 * Create a command.
	 * @param FeedRepository $feedRepository
	 * @param MessageBusInterface $bus
	 */
	public function __construct(FeedRepository $feedRepository, MessageBusInterface $bus) {
		parent::__construct(self::$defaultName);

		$this->feedRepository = $feedRepository;
		$this->bus = $bus;
	}

	/**
	 * @inheritDoc
	 */
	protected function configure() {
		$this->setDescription('Dispatches a message to parse all feeds and store relevant episodes');
	}

	/**
	 * @inheritDoc
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		foreach ($this->feedRepository->findAll() as $feed) {
			$this->bus->dispatch(new ParseFeedMessage($feed));
		}

		$output->write('Dispatched message to parse all feeds and store relevant episodes');

		return Command::SUCCESS;
	}
}
