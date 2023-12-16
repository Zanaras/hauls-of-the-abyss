<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Room {
	private ?string $visits = null;
	private ?int $id = null;
	private Collection $characters;
	private Collection $exits;
	private Collection $entrances;
	private ?Dungeon $dungeon = null;
	private ?RoomType $type = null;
	private ?bool $dungeonExit = null;
	private ?array $modifiers = null;
	private ?int $pathRoll = null;
	private ?int $range = null;
	private ?Floor $floor = null;
	private ?RoomType $altType = null;
	private ?Floor $leavesToFloor = null;
	private ?Floor $entersToFloor = null;
	private Collection $monsters;
	private array $DIRECTIONS = [
   		0 => "N",
   		1 => "NE",
   		2 => "E",
   		3 => "SE",
   		4 => "S",
   		5 => "SW",
   		6 => "W",
   		7 => "NW",
   	];

	public function __construct() {
   		$this->characters = new ArrayCollection();
   		$this->exits = new ArrayCollection();
   		$this->entrances = new ArrayCollection();
   		$this->monsters = new ArrayCollection();
   	}

	public function findAvailableDirection(): string|false {
		$i = rand(0,7);
		$total = 1;
		# Begin Loop.
		while (true) {
			$checking = $this->DIRECTIONS[$i];
			foreach ($this->exits as $exit) {
				if ($exit->getDirection() === $checking) {
					break;
				} else {
					return $checking;
				}
			}
			# Switch $i to next key to check.
			if ($i === 7) {
				$i = 0;
			} else {
				$i++;
			}
			$total++;
			# Check if there are no more directions and fail out.
			if ($total > count($this->DIRECTIONS)) {
				return false;
			}
		}
	}

	public function findFromDirection(Room $oldRoom, bool $full = false) {
		foreach ($this->exits as $exit) {
			if ($exit->getToRoom() === $oldRoom) {
				return $exit->getDirection();
			}
		}
		return "???";
	}

	public function getVisits(): ?string {
   		return $this->visits;
   	}

	public function setVisits(string $visits): static {
   		$this->visits = $visits;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	/**
	 * @return Collection<int, Character>
	 */
	public function getCharacters(): Collection {
   		return $this->characters;
   	}

	public function addCharacter(Character $character): static {
   		if (!$this->characters->contains($character)) {
   			$this->characters->add($character);
   			$character->setRoom($this);
   		}
   
   		return $this;
   	}

	public function removeCharacter(Character $character): static {
   		if ($this->characters->removeElement($character)) {
   			// set the owning side to null (unless already changed)
   			if ($character->getRoom() === $this) {
   				$character->setRoom(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Transit>
	 */
	public function getExits(): Collection {
   		return $this->exits;
   	}

	public function addExit(Transit $exit): static {
   		if (!$this->exits->contains($exit)) {
   			$this->exits->add($exit);
   			$exit->setFromRoom($this);
   		}
   
   		return $this;
   	}

	public function removeExit(Transit $exit): static {
   		if ($this->exits->removeElement($exit)) {
   			// set the owning side to null (unless already changed)
   			if ($exit->getFromRoom() === $this) {
   				$exit->setFromRoom(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Transit>
	 */
	public function getEntrances(): Collection {
   		return $this->entrances;
   	}

	public function addEntrance(Transit $entrance): static {
   		if (!$this->entrances->contains($entrance)) {
   			$this->entrances->add($entrance);
   			$entrance->setToRoom($this);
   		}
   
   		return $this;
   	}

	public function removeEntrance(Transit $entrance): static {
   		if ($this->entrances->removeElement($entrance)) {
   			// set the owning side to null (unless already changed)
   			if ($entrance->getToRoom() === $this) {
   				$entrance->setToRoom(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Monster>
	 */
	public function getMonsters(): Collection {
   		return $this->monsters;
   	}

	public function addMonster(Monster $monster): static {
   		if (!$this->monsters->contains($monster)) {
   			$this->monsters->add($monster);
   			$monster->setRoom($this);
   		}
   
   		return $this;
   	}

	public function removeMonster(Monster $monster): static {
   		if ($this->monsters->removeElement($monster)) {
   			// set the owning side to null (unless already changed)
   			if ($monster->getRoom() === $this) {
   				$monster->setRoom(null);
   			}
   		}
   
   		return $this;
   	}

	public function getDungeon(): ?Dungeon {
   		return $this->dungeon;
   	}

	public function setDungeon(?Dungeon $dungeon): static {
   		$this->dungeon = $dungeon;
   
   		return $this;
   	}

	public function getType(): ?RoomType {
   		return $this->type;
   	}

	public function setType(?RoomType $type): static {
   		$this->type = $type;
   
   		return $this;
   	}

	public function isDungeonExit(): ?bool {
   		return $this->dungeonExit;
   	}

	public function setDungeonExit(bool $dungeonExit): static {
   		$this->dungeonExit = $dungeonExit;
   
   		return $this;
   	}

	public function getModifiers(): ?array {
   		return $this->modifiers;
   	}

	public function setModifiers(?array $modifiers): static {
   		$this->modifiers = $modifiers;
   
   		return $this;
   	}

	public function getPathRoll(): ?int {
   		return $this->pathRoll;
   	}

	public function setPathRoll(int $pathRoll): static {
   		$this->pathRoll = $pathRoll;
   
   		return $this;
   	}

	public function getFloor(): ?Floor {
   		return $this->floor;
   	}

	public function setFloor(?Floor $floor): static {
   		$this->floor = $floor;
   
   		return $this;
   	}

	public function getAltType(): ?RoomType {
   		return $this->altType;
   	}

	public function setAltType(?RoomType $altType): static {
   		$this->altType = $altType;
   
   		return $this;
   	}

	public function getRange(): ?int {
   		return $this->range;
   	}

	public function setRange(int $range): static {
   		$this->range = $range;
   
   		return $this;
   	}

	/**
	 * Returns the floor that this one provides connection to.
	 *
	 * @return Floor|null
	 */
	public function getLeavesToFloor(): ?Floor {
   		return $this->leavesToFloor;
   	}

	/**
	 * Sets another floor other than the one this is on as a floor this room provides connection to.
	 *
	 * @param Floor|null $leavesToFloor
	 *
	 * @return $this
	 */
	public function setLeavesToFloor(?Floor $leavesToFloor): static {
   		$this->leavesToFloor = $leavesToFloor;
   
   		return $this;
   	}

	public function getEntersToFloor(): ?Floor {
   		return $this->entersToFloor;
   	}

	public function setEntersToFloor(?Floor $entersToFloor): static {
   		$this->entersToFloor = $entersToFloor;
   
   		return $this;
   	}
}
