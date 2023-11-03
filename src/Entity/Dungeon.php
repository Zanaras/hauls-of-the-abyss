<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Dungeon {
	private ?string $type = null;
	private ?string $name = null;
	private ?int $id = null;
	private Collection $rooms;

    private Collection $characters;

    private Collection $floors;

	public function __construct() {
                              		$this->rooms = new ArrayCollection();
                                $this->characters = new ArrayCollection();
                                $this->floors = new ArrayCollection();
                              	}

	public function getType(): ?string {
                              		return $this->type;
                              	}

	public function setType(string $type): static {
                              		$this->type = $type;
                              
                              		return $this;
                              	}

	public function getName(): ?string {
                              		return $this->name;
                              	}

	public function setName(string $name): static {
                              		$this->name = $name;
                              
                              		return $this;
                              	}

	public function getId(): ?int {
                              		return $this->id;
                              	}

	/**
	 * @return Collection<int, Room>
	 */
	public function getRooms(): Collection {
                              		return $this->rooms;
                              	}

	public function addRoom(Room $room): static {
                              		if (!$this->rooms->contains($room)) {
                              			$this->rooms->add($room);
                              			$room->setDungeon($this);
                              		}
                              
                              		return $this;
                              	}

	public function removeRoom(Room $room): static {
                              		if ($this->rooms->removeElement($room)) {
                              			// set the owning side to null (unless already changed)
                              			if ($room->getDungeon() === $this) {
                              				$room->setDungeon(null);
                              			}
                              		}
                              
                              		return $this;
                              	}

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): static
    {
        if (!$this->characters->contains($character)) {
            $this->characters->add($character);
            $character->setDungeon($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): static
    {
        if ($this->characters->removeElement($character)) {
            // set the owning side to null (unless already changed)
            if ($character->getDungeon() === $this) {
                $character->setDungeon(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Floor>
     */
    public function getFloors(): Collection
    {
        return $this->floors;
    }

    public function addFloor(Floor $floor): static
    {
        if (!$this->floors->contains($floor)) {
            $this->floors->add($floor);
            $floor->setDungeon($this);
        }

        return $this;
    }

    public function removeFloor(Floor $floor): static
    {
        if ($this->floors->removeElement($floor)) {
            // set the owning side to null (unless already changed)
            if ($floor->getDungeon() === $this) {
                $floor->setDungeon(null);
            }
        }

        return $this;
    }
}
