<?php

namespace App\Entity;

use App\Repository\FeedItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Representation of an item from a feed.
 * @ORM\Entity(repositoryClass=FeedItemRepository::class)
 * @UniqueEntity("checksum")
 */
class FeedItem {
	/**
	 * The ID.
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * The title.
	 * @Assert\NotBlank
	 * @ORM\Column(type="string", length=255)
	 */
	private string $title;

	/**
	 * The download link.
	 * @Assert\NotBlank
	 * @Assert\Url
	 * @ORM\Column(type="string", length=255)
	 */
	private string $link;

	/**
	 * The description.
	 * @Assert\NotBlank
	 * @ORM\Column(type="string", length=255)
	 */
	private string $description;

	/**
	 * Checksum for this item.
	 * @Assert\NotBlank
	 * @ORM\Column(type="string", length=255)
	 */
	private string $checksum;

	/**
	 * Datetime of creation.
	 * @ORM\Column(type="datetime")
	 */
	private \DateTimeInterface $createdAt;

	/**
	 * Create a feed item.
	 */
	public function __construct() {
		$this->setCreatedAt(new \DateTime());
	}

	/**
	 * Represent this entity as a string.
	 * @return string
	 */
	public function __toString(): string {
		return $this->getTitle();
	}

	/**
	 * Get the ID.
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get the title.
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Set the title.
	 * @param string $title
	 * @return $this
	 */
	public function setTitle(string $title): self {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get the download link.
	 * @return string
	 */
	public function getLink(): string {
		return $this->link;
	}

	/**
	 * Set the link.
	 * @param string $link
	 * @return $this
	 */
	public function setLink(string $link): self {
		$this->link = $link;

		return $this;
	}

	/**
	 * Get the description.
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set the description.
	 * @param string $description
	 * @return $this
	 */
	public function setDescription(string $description): self {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get the datetime of creation.
	 * @return \DateTimeInterface
	 */
	public function getCreatedAt(): \DateTimeInterface {
		return $this->createdAt;
	}

	/**
	 * Get the checksum.
	 * @return string
	 */
	public function getChecksum(): string {
		return $this->checksum;
	}

	/**
	 * Set the checksum.
	 * @param string $checksum
	 * @return $this
	 */
	public function setChecksum(string $checksum): self {
		$this->checksum = $checksum;

		return $this;
	}

	/**
	 * Generates the checksum and returns it.
	 * @return string
	 */
	public function generateChecksum(): string {
		$this->setChecksum(sha1(
			$this->getTitle()
			. $this->getLink()
			. $this->getDescription()
		));

		return $this->getChecksum();
	}

	/**
	 * Set the datetime of creation.
	 * @param \DateTimeInterface $createdAt
	 * @return $this
	 */
	public function setCreatedAt(\DateTimeInterface $createdAt): self {
		$this->createdAt = $createdAt;

		return $this;
	}
}
