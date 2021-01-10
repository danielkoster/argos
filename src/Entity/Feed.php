<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FeedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Representation of a feed.
 * @ApiResource(
 *     normalizationContext={"groups"={"user:read"}},
 *     denormalizationContext={"groups"={"user:write"}},
 * )
 * @ORM\Entity(repositoryClass=FeedRepository::class)
 */
class Feed {
	/**
	 * The ID.
	 * @Groups({"user:read"})
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * The name.
	 * @Assert\NotBlank
	 * @ORM\Column(type="string", length=255)
	 */
	private string $name;

	/**
	 * The URL.
	 * @Assert\NotBlank
	 * @Assert\Url
	 * @ORM\Column(type="string", length=255)
	 */
	private string $url;

	/**
	 * The feed processor ID's.
	 * @Assert\Choice(choices="{FeedProcessorInterface::STRATEGY_OPTIONS}")
	 * @Assert\NotBlank
	 * @ORM\Column(type="array")
	 */
	private array $processorIds = [];

	/**
	 * Get the ID.
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get the name.
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set the name.
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the URL.
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * Set the URL.
	 * @param string $url
	 * @return $this
	 */
	public function setUrl(string $url): self {
		$this->url = $url;

		return $this;
	}

	/**
	 * The feed processor ID's.
	 * @return string[]
	 */
	public function getProcessorIds(): array {
		return $this->processorIds;
	}

	/**
	 * Set the feed processor ID's.
	 * @param string[] $processorIds
	 * @return $this
	 */
	public function setProcessorIds(array $processorIds): self {
		$this->processorIds = $processorIds;

		return $this;
	}
}
