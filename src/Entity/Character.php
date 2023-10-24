<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Character {
	private ?string $name = null;
	private ?bool $alive = null;
	private ?bool $retired = null;
	private ?DateTimeInterface $retiredOn = null;
	private ?DateTimeInterface $created = null;
	private ?DateTimeInterface $lastAccess = null;
	private ?bool $slumbering = null;
	private ?bool $special = null;
	private ?int $wounded = null;
	private ?int $id = null;
	private Collection $userLogs;
	private ?User $user = null;
	private ?Race $race = null;
	private ?int $areaCode = null;
	private ?string $gender = null;
	private Collection $descriptions;
	private ?Description $description = null;
	private Collection $updatedDescriptions;

	public function isActive($slumbererIsActive = true): bool {
   		if (!$this->alive) {
   			return false;
   		}
   		if ($this->retired) {
   			return false;
   		}
   		if (!$slumbererIsActive && $this->slumbering) {
   			return false;
   		}
   		return true;
   	}

	public function __construct() {
   		$this->userLogs = new ArrayCollection();
   		$this->descriptions = new ArrayCollection();
   		$this->updatedDescriptions = new ArrayCollection();
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

	public function getAreaCode(): ?int {
   		return $this->areaCode;
   	}

	public function setAreaCode(int $areaCode): static {
   		$this->areaCode = $areaCode;
   
   		return $this;
   	}

	public function getGender(): ?string {
   		return $this->gender;
   	}

	public function setGender(string $gender): static {
   		$this->gender = $gender;
   
   		return $this;
   	}

	public function getDescription(): ?Description {
   		return $this->description;
   	}

	public function setDescription(?Description $description): static {
   		// unset the owning side of the relation if necessary
   		if ($description === null && $this->description !== null) {
   			$this->description->setActiveCharacter(null);
   		}

   		// set the owning side of the relation if necessary
   		if ($description !== null && $description->getActiveUser() !== $this) {
   			$description->setActiveCharacter($this);
   		}

   		$this->description = $description;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Description>
	 */
	public function getDescriptions(): Collection {
   		return $this->descriptions;
   	}

	public function addDescription(Description $description): static {
   		if (!$this->descriptions->contains($description)) {
   			$this->descriptions->add($description);
   			$description->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeDescription(Description $description): static {
   		if ($this->descriptions->removeElement($description)) {
   			// set the owning side to null (unless already changed)
   			if ($description->getCharacter() === $this) {
   				$description->setCharacter(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Description>
	 */
	public function getUpdatedDescriptions(): Collection {
   		return $this->updatedDescriptions;
   	}

	public function addUpdatedDescription(Description $updatedDescription): static {
   		if (!$this->updatedDescriptions->contains($updatedDescription)) {
   			$this->updatedDescriptions->add($updatedDescription);
   			$updatedDescription->setUpdater($this);
   		}
   
   		return $this;
   	}

	public function removeUpdatedDescription(Description $updatedDescription): static {
   		if ($this->updatedDescriptions->removeElement($updatedDescription)) {
   			// set the owning side to null (unless already changed)
   			if ($updatedDescription->getUpdater() === $this) {
   				$updatedDescription->setUpdater(null);
   			}
   		}
   
   		return $this;
   	}
}
