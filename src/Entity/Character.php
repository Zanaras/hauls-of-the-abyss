<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Character {
	private ?string $name = null;
	private ?bool $alive = null;
	private ?bool $retired = null;
	private ?DateTimeInterface $retired_on = null;
	private ?int $magic = null;
	private ?DateTimeInterface $created = null;
	private ?DateTimeInterface $last_access = null;
	private ?bool $slumbering = null;
	private ?bool $special = null;
	private ?int $wounded = null;
	private ?int $id = null;
	private Collection $userLogs;
	private ?User $user = null;
	private ?Race $race = null;

	public function __construct() {
   		$this->userLogs = new ArrayCollection();
   	}

	public function getName(): ?string {
   		return $this->name;
   	}

	public function setName(string $name): static {
   		$this->name = $name;
   
   		return $this;
   	}

	public function isAlive(): ?bool {
   		return $this->alive;
   	}

	public function setAlive(bool $alive): static {
   		$this->alive = $alive;
   
   		return $this;
   	}

	public function isRetired(): ?bool {
   		return $this->retired;
   	}

	public function setRetired(?bool $retired): static {
   		$this->retired = $retired;
   
   		return $this;
   	}

	public function getRetiredOn(): ?DateTimeInterface {
   		return $this->retired_on;
   	}

	public function setRetiredOn(?DateTimeInterface $retired_on): static {
   		$this->retired_on = $retired_on;
   
   		return $this;
   	}

	public function getMagic(): ?int {
   		return $this->magic;
   	}

	public function setMagic(?int $magic): static {
   		$this->magic = $magic;
   
   		return $this;
   	}

	public function getCreated(): ?DateTimeInterface {
   		return $this->created;
   	}

	public function setCreated(DateTimeInterface $created): static {
   		$this->created = $created;
   
   		return $this;
   	}

	public function getLastAccess(): ?DateTimeInterface {
   		return $this->last_access;
   	}

	public function setLastAccess(DateTimeInterface $last_access): static {
   		$this->last_access = $last_access;
   
   		return $this;
   	}

	public function isSlumbering(): ?bool {
   		return $this->slumbering;
   	}

	public function setSlumbering(bool $slumbering): static {
   		$this->slumbering = $slumbering;
   
   		return $this;
   	}

	public function isSpecial(): ?bool {
   		return $this->special;
   	}

	public function setSpecial(bool $special): static {
   		$this->special = $special;
   
   		return $this;
   	}

	public function getWounded(): ?int {
   		return $this->wounded;
   	}

	public function setWounded(int $wounded): static {
   		$this->wounded = $wounded;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	/**
	 * @return Collection<int, UserLog>
	 */
	public function getUserLogs(): Collection {
   		return $this->userLogs;
   	}

	public function addUserLog(UserLog $userLog): static {
   		if (!$this->userLogs->contains($userLog)) {
   			$this->userLogs->add($userLog);
   			$userLog->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeUserLog(UserLog $userLog): static {
   		if ($this->userLogs->removeElement($userLog)) {
   			// set the owning side to null (unless already changed)
   			if ($userLog->getCharacter() === $this) {
   				$userLog->setCharacter(null);
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

	public function getRace(): ?Race {
   		return $this->race;
   	}

	public function setRace(?Race $race): static {
   		$this->race = $race;
   
   		return $this;
   	}
}
