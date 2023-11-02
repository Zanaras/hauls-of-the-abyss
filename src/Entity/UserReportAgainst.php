<?php

namespace App\Entity;

class UserReportAgainst {
	private ?\DateTimeInterface $date = null;
	private ?string $id = null;
	private ?User $added_by = null;
	private ?User $user = null;
	private ?UserReport $report = null;

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

	public function getAddedBy(): ?User {
		return $this->added_by;
	}

	public function setAddedBy(?User $added_by): static {
		$this->added_by = $added_by;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): static {
		$this->user = $user;

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
