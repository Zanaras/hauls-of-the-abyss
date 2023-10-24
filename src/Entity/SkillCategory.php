<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SkillCategory {
	private ?string $name = null;
	private ?int $id = null;
	private Collection $sub_categories;
	private Collection $skills;
	private ?self $category = null;

	public function __construct() {
		$this->sub_categories = new ArrayCollection();
		$this->skills = new ArrayCollection();
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
	 * @return Collection<int, SkillCategory>
	 */
	public function getSubCategories(): Collection {
		return $this->sub_categories;
	}

	public function addSubCategory(self $subCategory): static {
		if (!$this->sub_categories->contains($subCategory)) {
			$this->sub_categories->add($subCategory);
			$subCategory->setCategory($this);
		}

		return $this;
	}

	public function removeSubCategory(self $subCategory): static {
		if ($this->sub_categories->removeElement($subCategory)) {
			// set the owning side to null (unless already changed)
			if ($subCategory->getCategory() === $this) {
				$subCategory->setCategory(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, SkillType>
	 */
	public function getSkills(): Collection {
		return $this->skills;
	}

	public function addSkill(SkillType $skill): static {
		if (!$this->skills->contains($skill)) {
			$this->skills->add($skill);
			$skill->setCategory($this);
		}

		return $this;
	}

	public function removeSkill(SkillType $skill): static {
		if ($this->skills->removeElement($skill)) {
			// set the owning side to null (unless already changed)
			if ($skill->getCategory() === $this) {
				$skill->setCategory(null);
			}
		}

		return $this;
	}

	public function getCategory(): ?self {
		return $this->category;
	}

	public function setCategory(?self $category): static {
		$this->category = $category;

		return $this;
	}
}
