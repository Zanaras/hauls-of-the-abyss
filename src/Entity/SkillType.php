<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SkillType {
	private ?string $name = null;
	private ?int $id = null;
	private Collection $origins;
	private ?SkillCategory $category = null;

	public function __construct() {
		$this->origins = new ArrayCollection();
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return Collection<int, OriginSkill>
	 */
	public function getOrigins(): Collection {
		return $this->origins;
	}

	public function addOrigin(OriginSkill $origin): static {
		if (!$this->origins->contains($origin)) {
			$this->origins->add($origin);
			$origin->setSkill($this);
		}

		return $this;
	}

	public function removeOrigin(OriginSkill $origin): static {
		if ($this->origins->removeElement($origin)) {
			// set the owning side to null (unless already changed)
			if ($origin->getSkill() === $this) {
				$origin->setSkill(null);
			}
		}

		return $this;
	}

	public function getCategory(): ?SkillCategory {
		return $this->category;
	}

	public function setCategory(?SkillCategory $category): static {
		$this->category = $category;

		return $this;
	}
}
