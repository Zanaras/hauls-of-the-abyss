<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Journal {
	private ?string $topic = null;
	private ?string $entry = null;
	private ?\DateTimeInterface $date = null;
	private ?int $cycle = null;
	private ?bool $public = null;
	private ?bool $graphic = null;
	private ?bool $ooc = null;
	private ?bool $pending_review = null;
	private ?bool $GM_reviewed = null;
	private ?bool $GM_private = null;
	private ?bool $GM_graphic = null;
	private ?string $language = null;
	private ?int $id = null;
	private Collection $reports;
	private ?Character $character = null;

	public function __construct() {
   		$this->reports = new ArrayCollection();
   	}

	public function getTopic(): ?string {
   		return $this->topic;
   	}

	public function setTopic(string $topic): static {
   		$this->topic = $topic;
   
   		return $this;
   	}

	public function getEntry(): ?string {
   		return $this->entry;
   	}

	public function setEntry(string $entry): static {
   		$this->entry = $entry;
   
   		return $this;
   	}

	public function getDate(): ?\DateTimeInterface {
   		return $this->date;
   	}

	public function setDate(\DateTimeInterface $date): static {
   		$this->date = $date;
   
   		return $this;
   	}

	public function getCycle(): ?int {
   		return $this->cycle;
   	}

	public function setCycle(int $cycle): static {
   		$this->cycle = $cycle;
   
   		return $this;
   	}

	public function isPublic(): ?bool {
   		return $this->public;
   	}

	public function setPublic(bool $public): static {
   		$this->public = $public;
   
   		return $this;
   	}

	public function isGraphic(): ?bool {
   		return $this->graphic;
   	}

	public function setGraphic(bool $graphic): static {
   		$this->graphic = $graphic;
   
   		return $this;
   	}

	public function isOoc(): ?bool {
   		return $this->ooc;
   	}

	public function setOoc(bool $ooc): static {
   		$this->ooc = $ooc;
   
   		return $this;
   	}

	public function isPendingReview(): ?bool {
   		return $this->pending_review;
   	}

	public function setPendingReview(bool $pending_review): static {
   		$this->pending_review = $pending_review;
   
   		return $this;
   	}

	public function isGMReviewed(): ?bool {
   		return $this->GM_reviewed;
   	}

	public function setGMReviewed(bool $GM_reviewed): static {
   		$this->GM_reviewed = $GM_reviewed;
   
   		return $this;
   	}

	public function isGMPrivate(): ?bool {
   		return $this->GM_private;
   	}

	public function setGMPrivate(?bool $GM_private): static {
   		$this->GM_private = $GM_private;
   
   		return $this;
   	}

	public function isGMGraphic(): ?bool {
   		return $this->GM_graphic;
   	}

	public function setGMGraphic(?bool $GM_graphic): static {
   		$this->GM_graphic = $GM_graphic;
   
   		return $this;
   	}

	public function getLanguage(): ?string {
   		return $this->language;
   	}

	public function setLanguage(string $language): static {
   		$this->language = $language;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	/**
	 * @return Collection<int, UserReport>
	 */
	public function getReports(): Collection {
   		return $this->reports;
   	}

	public function addReport(UserReport $report): static {
   		if (!$this->reports->contains($report)) {
   			$this->reports->add($report);
   			$report->setJournal($this);
   		}
   
   		return $this;
   	}

	public function removeReport(UserReport $report): static {
   		if ($this->reports->removeElement($report)) {
   			// set the owning side to null (unless already changed)
   			if ($report->getJournal() === $this) {
   				$report->setJournal(null);
   			}
   		}
   
   		return $this;
   	}

	public function getCharacter(): ?Character {
   		return $this->character;
   	}

	public function setCharacter(?Character $character): static {
   		$this->character = $character;
   
   		return $this;
   	}
}
