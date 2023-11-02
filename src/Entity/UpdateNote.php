<?php

namespace App\Entity;

class UpdateNote {
	private ?\DateTimeInterface $ts = null;
	private ?string $version = null;
	private ?string $title = null;
	private ?string $text = null;
	private ?int $id = null;

	public function getTs(): ?\DateTimeInterface {
		return $this->ts;
	}

	public function setTs(\DateTimeInterface $ts): static {
		$this->ts = $ts;

		return $this;
	}

	public function getVersion(): ?string {
		return $this->version;
	}

	public function setVersion(string $version): static {
		$this->version = $version;

		return $this;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle(string $title): static {
		$this->title = $title;

		return $this;
	}

	public function getText(): ?string {
		return $this->text;
	}

	public function setText(string $text): static {
		$this->text = $text;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}
}
