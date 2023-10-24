<?php

namespace App\Entity;

class OriginSkill {
	private ?int $id = null;
	private ?SkillType $skill = null;
	private ?Origin $origin = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getSkill(): ?SkillType {
		return $this->skill;
	}

	public function setSkill(?SkillType $skill): static {
		$this->skill = $skill;

		return $this;
	}

	public function getOrigin(): ?Origin {
		return $this->origin;
	}

	public function setOrigin(?Origin $origin): static {
		$this->origin = $origin;

		return $this;
	}
}
