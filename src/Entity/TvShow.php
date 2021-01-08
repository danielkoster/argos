<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TvShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Representation of a TV show.
 * @ApiResource(
 *     normalizationContext={"groups"={"user:read"}},
 *     denormalizationContext={"groups"={"user:write"}},
 * )
 * @ORM\Entity(repositoryClass=TvShowRepository::class)
 */
class TvShow {
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
	 * @Groups({"user:read", "user:write"})
	 * @ORM\Column(type="string", length=255)
	 */
	private string $name;

	/**
	 * Season to start following.
	 * @Assert\NotBlank
	 * @Assert\Positive
	 * @Groups({"user:read", "user:write"})
	 * @ORM\Column(type="integer")
	 */
	private int $followFromSeason;

	/**
	 * Episode to start following.
	 * @Assert\NotBlank
	 * @Assert\Positive
	 * @Groups({"user:read", "user:write"})
	 * @ORM\Column(type="integer")
	 */
	private int $followFromEpisode;

	/**
	 * The minimum quality to download.
	 * @Assert\NotBlank
	 * @Assert\PositiveOrZero
	 * @Groups({"user:read", "user:write"})
	 * @ORM\Column(type="integer")
	 */
	private int $minimumQuality;

	/**
	 * Minutes to wait for a high quality episode.
	 * @Assert\NotBlank
	 * @Assert\PositiveOrZero
	 * @Groups({"user:read", "user:write"})
	 * @ORM\Column(type="integer")
	 */
	private int $highQualityWaitingTime;

	/**
	 * Episodes belonging to this tvShow.
	 * @ORM\OneToMany(targetEntity=Episode::class, mappedBy="tvShow", orphanRemoval=true)
	 * @Groups({"user:read"})
	 */
	private Collection $episodes;

	/**
	 * Episode candidates belonging to this tvShow.
	 * @ORM\OneToMany(targetEntity=EpisodeCandidate::class, mappedBy="tvShow", orphanRemoval=true)
	 */
	private Collection $episodeCandidates;

	/**
	 * Create a tvShow.
	 */
	public function __construct() {
		$this->episodes = new ArrayCollection();
		$this->episodeCandidates = new ArrayCollection();
	}

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
	 * Get the season to start following.
	 * @return int
	 */
	public function getFollowFromSeason(): int {
		return $this->followFromSeason;
	}

	/**
	 * Set the season to start following.
	 * @param int $followFromSeason
	 * @return $this
	 */
	public function setFollowFromSeason(int $followFromSeason): self {
		$this->followFromSeason = $followFromSeason;

		return $this;
	}

	/**
	 * Get the episode to start following.
	 * @return int
	 */
	public function getFollowFromEpisode(): int {
		return $this->followFromEpisode;
	}

	/**
	 * Set the episode to start following.
	 * @param int $followFromEpisode
	 * @return $this
	 */
	public function setFollowFromEpisode(int $followFromEpisode): self {
		$this->followFromEpisode = $followFromEpisode;

		return $this;
	}

	/**
	 * Get the minimum quality of episodes to download.
	 * @return int
	 */
	public function getMinimumQuality(): int {
		return $this->minimumQuality;
	}

	/**
	 * Set the minimum quality of episodes to download.
	 * @param int $minimumQuality
	 * @return $this
	 */
	public function setMinimumQuality(int $minimumQuality): self {
		$this->minimumQuality = $minimumQuality;

		return $this;
	}

	/**
	 * Get the waiting time for a high quality episode.
	 * @return int
	 */
	public function getHighQualityWaitingTime(): int {
		return $this->highQualityWaitingTime;
	}

	/**
	 * Set the waiting time for a high quality episode.
	 * @param int $highQualityWaitingTime
	 * @return $this
	 */
	public function setHighQualityWaitingTime(int $highQualityWaitingTime): self {
		$this->highQualityWaitingTime = $highQualityWaitingTime;

		return $this;
	}

	/**
	 * Get a list of episodes beloning to this tvShow.
	 * @return Collection|Episode[]
	 */
	public function getEpisodes(): Collection {
		return $this->episodes;
	}

	/**
	 * Remove an episode from this tvShow.
	 * @param Episode $episode
	 * @return $this
	 */
	public function removeEpisode(Episode $episode): self {
		$this->episodes->removeElement($episode);

		return $this;
	}

	/**
	 * Get a list of episodes beloning to this tvShow.
	 * @return Collection|EpisodeCandidate[]
	 */
	public function getEpisodeCandidates(): Collection {
		return $this->episodeCandidates;
	}

	/**
	 * Remove an episode candidate from this tvShow.
	 * @param EpisodeCandidate $episodeCandidate
	 * @return $this
	 */
	public function removeEpisodeCandidate(EpisodeCandidate $episodeCandidate): self {
		$this->episodeCandidates->removeElement($episodeCandidate);

		return $this;
	}
}
