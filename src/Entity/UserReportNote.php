<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class UserReportNote {
	private ?string $text = null;
	private ?\DateTimeInterface $date = null;
	private ?bool $pending = null;
	private ?string $verdict = null;
	private ?string $id = null;
	private ?User $from = null;
	private ?UserReport $report = null;

	public function getText(): ?string {
   		return $this->text;
   	}

	public function setText(string $text): static {
   		$this->text = $text;
   
   		return $this;
   	}

	public function getDate(): ?\DateTimeInterface {
   		return $this->date;
   	}

	public function setDate(\DateTimeInterface $date): static {
   		$this->date = $date;
   
   		return $this;
   	}

	public function isPending(): ?bool {
   		return $this->pending;
   	}

	public function setPending(bool $pending): static {
   		$this->pending = $pending;
   
   		return $this;
   	}

	public function getVerdict(): ?string {
   		return $this->verdict;
   	}

	public function setVerdict(string $verdict): static {
   		$this->verdict = $verdict;
   
   		return $this;
   	}

	public function getId(): ?string {
   		return $this->id;
   	}

	public function getFrom(): ?User {
   		return $this->from;
   	}

	public function setFrom(?User $from): static {
   		$this->from = $from;
   
   		return $this;
   	}

	public function getReport(): ?UserReport {
   		return $this->report;
   	}

	public function setReport(?UserReport $report): static {
   		$this->report = $report;
   
   		return $this;
   	}
}
