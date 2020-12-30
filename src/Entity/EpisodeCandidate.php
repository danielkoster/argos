<?php

namespace App\Entity;

use App\Repository\EpisodeCandidateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Representation of an episode which can be downloaded.
 * @ORM\Entity(repositoryClass=EpisodeCandidateRepository::class)
 */
class EpisodeCandidate extends Episode {
}
