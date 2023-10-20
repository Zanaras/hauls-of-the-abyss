<?php

namespace App\Entity;

class AppSetting {
	private ?string $name = null;
	private ?string $type = null;
	private ?string $value = null;
	private ?int $id = null;

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	public function getValue(): ?string {
		return $this->value;
	}

	public function setValue(string $value): static {
		$this->value = $value;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}
}
