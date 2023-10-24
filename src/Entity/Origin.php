<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Origin {
	private ?string $name = null;
	private ?string $shortDescription = null;
	private ?string $longDescription = null;
	private ?int $id = null;
	private Collection $skills;
	private ?bool $public = null;

	public function __construct() {
   		$this->skills = new ArrayCollection();
   	}

	public function getName(): ?string {
   		return $this->name;
   	}

	public function setName(string $name): static {
   		$this->name = $name;
   
   		return $this;
   	}

	public function getShortDescription(): ?string {
   		return $this->shortDescription;
   	}

	public function setShortDescription(string $shortDescription): static {
   		$this->shortDescription = $shortDescription;
   
   		return $this;
   	}

	public function getLongDescription(): ?string {
   		return $this->longDescription;
   	}

	public function setLongDescription(string $longDescription): static {
   		$this->longDescription = $longDescription;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	/**
	 * @return Collection<int, OriginSkill>
	 */
	public function getSkills(): Collection {
   		return $this->skills;
   	}

	public function addSkill(OriginSkill $skill): static {
   		if (!$this->skills->contains($skill)) {
   			$this->skills->add($skill);
   			$skill->setOrigin($this);
   		}
   
   		return $this;
   	}

	public function removeSkill(OriginSkill $skill): static {
   		if ($this->skills->removeElement($skill)) {
   			// set the owning side to null (unless already changed)
   			if ($skill->getOrigin() === $this) {
   				$skill->setOrigin(null);
   			}
   		}
   
   		return $this;
   	}

	public function isPublic(): ?bool {
   		return $this->public;
   	}

	public function setPublic(bool $public): static {
   		$this->public = $public;
   
   		return $this;
   	}
}
