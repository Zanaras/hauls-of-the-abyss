<?php

namespace App\Entity;

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

	public function evaluate(): float {
		$pract = $this->practice ?: 1;
		$theory = $this->theory ?: 1;
		#TODO: Smooth these out so there's less sharp changes.
		if ($pract >= $theory * 3) {
			# Practice is greater than triple of theory. Use practice but subtract a quarter.
			$score = $pract * 0.75;
		} elseif ($pract * 10 <= $theory) {
			# Practice is less than a tenth of theory. Use theory but remove four fifths.
			$score = $theory * 0.2;
		} else {
			$score = max($theory, $pract);
		}
		return $score;
	}

	public function getScore() {
		$char = $this->character;
		$scores = [$this->evaluate()];
		foreach ($char->getSkills() as $each) {
			if ($each->getCategory() === $this->category && $each !== $this) {
				$scores[] = $each->evaluate() / 2;
			}
		}
		return max($scores);
	}

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
