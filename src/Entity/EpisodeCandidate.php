<?php

namespace App\Entity;

use App\Repository\EpisodeCandidateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Representation of an episode which can be downloaded.
 * @ORM\Entity(repositoryClass=EpisodeCandidateRepository::class)
 */
class EpisodeCandidate implements EpisodeInterface {
	/**
	 * The ID.
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * The {@see TvShow} this episode belongs to.
	 * @Assert\NotBlank
	 * @ORM\ManyToOne(targetEntity=Show::class, inversedBy="episodeCandidates")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private TvShow $tvShow;

	/**
	 * The URL to download this episode.
	 * @Assert\NotBlank
	 * @Assert\Url
	 * @ORM\Column(type="string", length=255)
	 */
	private string $downloadLink;

	/**
	 * The season number.
	 * @Assert\NotBlank
	 * @Assert\Positive
	 * @ORM\Column(type="integer")
	 */
	private int $seasonNumber;

	/**
	 * The episode number.
	 * @Assert\NotBlank
	 * @Assert\Positive
	 * @ORM\Column(type="integer")
	 */
	private int $episodeNumber;

	/**
	 * The quality.
	 * @Assert\NotBlank
	 * @Assert\Positive
	 * @ORM\Column(type="integer")
	 */
	private int $quality;

	/**
	 * Datetime of creation.
	 * @ORM\Column(type="datetime")
	 */
	private \DateTimeInterface $createdAt;

	/**
	 * Create an episode.
	 */
	public function __construct() {
		$this->setCreatedAt(new \DateTime());
	}

	/**
	 * Represent this entity as a string.
	 * @return string
	 */
	public function __toString(): string {
		return sprintf(
			'%s - S%d - E%d - Q%d',
			$this->getTvShow()->getName(),
			$this->getSeasonNumber(),
			$this->getEpisodeNumber(),
			$this->getQuality()
		);
	}

	/**
	 * Get the ID.
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function getTvShow(): TvShow {
		return $this->tvShow;
	}

	/**
	 * Set the {@see TvShow} this episode belongs to.
	 * @param TvShow|null $tvShow
	 * @return $this
	 */
	public function setTvShow(TvShow $tvShow): self {
		$this->tvShow = $tvShow;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getDownloadLink(): string {
		return $this->downloadLink;
	}

	/**
	 * Set the URL to download this episode.
	 * @param string $downloadLink
	 * @return $this
	 */
	public function setDownloadLink(string $downloadLink): self {
		$this->downloadLink = $downloadLink;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSeasonNumber(): int {
		return $this->seasonNumber;
	}

	/**
	 * Set the season number.
	 * @param int $seasonNumber
	 * @return $this
	 */
	public function setSeasonNumber(int $seasonNumber): self {
		$this->seasonNumber = $seasonNumber;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getEpisodeNumber(): int {
		return $this->episodeNumber;
	}

	/**
	 * Set the episode number.
	 * @param int $episodeNumber
	 * @return $this
	 */
	public function setEpisodeNumber(int $episodeNumber): self {
		$this->episodeNumber = $episodeNumber;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getQuality(): int {
		return $this->quality;
	}

	/**
	 * Set the quality.
	 * @param int $quality
	 * @return $this
	 */
	public function setQuality(int $quality): self {
		$this->quality = $quality;

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
	 * Set the datetime of creation.
	 * @param \DateTimeInterface $createdAt
	 * @return $this
	 */
	public function setCreatedAt(\DateTimeInterface $createdAt): self {
		$this->createdAt = $createdAt;

		return $this;
	}
}
