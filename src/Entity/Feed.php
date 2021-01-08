<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FeedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Representation of a feed.
 * @ApiResource()
 * @ORM\Entity(repositoryClass=FeedRepository::class)
 */
class Feed {
	/**
	 * The ID.
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * The name.
	 * @ORM\Column(type="string", length=255)
	 */
	private string $name;

	/**
	 * The URL.
	 * @ORM\Column(type="string", length=255)
	 */
	private string $url;

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
	public function getName(): ?string {
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
	public function getUrl(): ?string {
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
}
