<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class UserReport {
	private ?string $type = null;
	private ?string $text = null;
	private ?bool $actioned = null;
	private ?\DateTimeInterface $date = null;
	private ?string $id = null;
	private Collection $notes;
	private Collection $against;
	private ?User $user = null;
	private ?Journal $journal = null;

	public function __construct() {
		$this->notes = new ArrayCollection();
		$this->against = new ArrayCollection();
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	public function getText(): ?string {
		return $this->text;
	}

	public function setText(string $text): static {
		$this->text = $text;

		return $this;
	}

	public function isActioned(): ?bool {
		return $this->actioned;
	}

	public function setActioned(bool $actioned): static {
		$this->actioned = $actioned;

		return $this;
	}

	public function getDate(): ?\DateTimeInterface {
		return $this->date;
	}

	public function setDate(\DateTimeInterface $date): static {
		$this->date = $date;

		return $this;
	}

	public function getId(): ?string {
		return $this->id;
	}

	/**
	 * @return Collection<int, UserReportNote>
	 */
	public function getNotes(): Collection {
		return $this->notes;
	}

	public function addNote(UserReportNote $note): static {
		if (!$this->notes->contains($note)) {
			$this->notes->add($note);
			$note->setReport($this);
		}

		return $this;
	}

	public function removeNote(UserReportNote $note): static {
		if ($this->notes->removeElement($note)) {
			// set the owning side to null (unless already changed)
			if ($note->getReport() === $this) {
				$note->setReport(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, UserReportAgainst>
	 */
	public function getAgainst(): Collection {
		return $this->against;
	}

	public function addAgainst(UserReportAgainst $against): static {
		if (!$this->against->contains($against)) {
			$this->against->add($against);
			$against->setReport($this);
		}

		return $this;
	}

	public function removeAgainst(UserReportAgainst $against): static {
		if ($this->against->removeElement($against)) {
			// set the owning side to null (unless already changed)
			if ($against->getReport() === $this) {
				$against->setReport(null);
			}
		}

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): static {
		$this->user = $user;

		return $this;
	}

	public function getJournal(): ?Journal {
		return $this->journal;
	}

	public function setJournal(?Journal $journal): static {
		$this->journal = $journal;

		return $this;
	}
}
