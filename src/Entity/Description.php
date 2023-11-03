<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class Description {
	private ?\DateTimeInterface $ts = null;
	private ?int $cycle = null;
	private ?string $text = null;
	private ?int $id = null;
	private ?Character $active_character = null;
	private ?User $active_user = null;
	private ?self $previous = null;
	private ?self $next = null;
	private ?Character $character = null;
	private ?User $user = null;
	private ?Character $updater = null;

	public function getTs(): ?\DateTimeInterface {
   		return $this->ts;
   	}

	public function setTs(\DateTimeInterface $ts): static {
   		$this->ts = $ts;
   
   		return $this;
   	}

	public function getCycle(): ?int {
   		return $this->cycle;
   	}

	public function setCycle(int $cycle): static {
   		$this->cycle = $cycle;
   
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

	public function getActiveCharacter(): ?Character {
   		return $this->active_character;
   	}

	public function setActiveCharacter(?Character $active_character): static {
   		$this->active_character = $active_character;
   
   		return $this;
   	}

	public function getActiveUser(): ?User {
   		return $this->active_user;
   	}

	public function setActiveUser(?User $active_user): static {
   		$this->active_user = $active_user;
   
   		return $this;
   	}

	public function getPrevious(): ?self {
   		return $this->previous;
   	}

	public function setPrevious(?self $previous): static {
   		$this->previous = $previous;
   
   		return $this;
   	}

	public function getNext(): ?self {
   		return $this->next;
   	}

	public function setNext(?self $next): static {
   		// unset the owning side of the relation if necessary
   		if (null === $next && null !== $this->next) {
   			$this->next->setPrevious(null);
   		}
   
   		// set the owning side of the relation if necessary
   		if (null !== $next && $next->getPrevious() !== $this) {
   			$next->setPrevious($this);
   		}
   
   		$this->next = $next;
   
   		return $this;
   	}

	public function getCharacter(): ?Character {
   		return $this->character;
   	}

	public function setCharacter(?Character $character): static {
   		$this->character = $character;
   
   		return $this;
   	}

	public function getUser(): ?User {
   		return $this->user;
   	}

	public function setUser(?User $user): static {
   		$this->user = $user;
   
   		return $this;
   	}

	public function getUpdater(): ?Character {
   		return $this->updater;
   	}

	public function setUpdater(?Character $updater): static {
   		$this->updater = $updater;
   
   		return $this;
   	}
}
