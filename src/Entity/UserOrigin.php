<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class UserOrigin {
	private ?\DateTimeInterface $dateUnlocked = null;
	private ?int $id = null;
	private ?User $user = null;
	private ?Origin $origin = null;

	public function getDateUnlocked(): ?\DateTimeInterface {
   		return $this->dateUnlocked;
   	}

	public function setDateUnlocked(\DateTimeInterface $dateUnlocked): static {
   		$this->dateUnlocked = $dateUnlocked;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	public function getUser(): ?User {
   		return $this->user;
   	}

	public function setUser(?User $user): static {
   		$this->user = $user;
   
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
