<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class Skill {
	private ?int $theory = null;
	private ?int $practice = null;
	private ?int $theory_high = null;
	private ?int $practice_high = null;
	private ?\DateTimeInterface $updated = null;
	private ?int $id = null;
	private ?Character $character = null;
	private ?SkillType $type = null;
	private ?SkillCategory $category = null;

	public function getTheory(): ?int {
   		return $this->theory;
   	}

	public function setTheory(int $theory): static {
   		$this->theory = $theory;
   
   		return $this;
   	}

	public function getPractice(): ?int {
   		return $this->practice;
   	}

	public function setPractice(int $practice): static {
   		$this->practice = $practice;
   
   		return $this;
   	}

	public function getTheoryHigh(): ?int {
   		return $this->theory_high;
   	}

	public function setTheoryHigh(int $theory_high): static {
   		$this->theory_high = $theory_high;
   
   		return $this;
   	}

	public function getPracticeHigh(): ?int {
   		return $this->practice_high;
   	}

	public function setPracticeHigh(int $practice_high): static {
   		$this->practice_high = $practice_high;
   
   		return $this;
   	}

	public function getUpdated(): ?\DateTimeInterface {
   		return $this->updated;
   	}

	public function setUpdated(\DateTimeInterface $updated): static {
   		$this->updated = $updated;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	public function getCharacter(): ?Character {
   		return $this->character;
   	}

	public function setCharacter(?Character $character): static {
   		$this->character = $character;
   
   		return $this;
   	}

	public function getType(): ?SkillType {
   		return $this->type;
   	}

	public function setType(?SkillType $type): static {
   		$this->type = $type;
   
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
