<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Floor
{
    private ?string $visits = null;

    private ?int $sprawl = null;

    private ?int $actualDepth = null;

    private ?int $relativeDepth = null;

    private ?int $pits = null;

    private ?string $type = null;

    private ?int $id = null;

    private Collection $rooms;

    private Collection $exits;

    private Collection $entrances;

    private ?Dungeon $dungeon = null;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->exits = new ArrayCollection();
        $this->entrances = new ArrayCollection();
    }

    public function getVisits(): ?string
    {
        return $this->visits;
    }

    public function setVisits(string $visits): static
    {
        $this->visits = $visits;

        return $this;
    }

    public function getSprawl(): ?int
    {
        return $this->sprawl;
    }

    public function setSprawl(int $sprawl): static
    {
        $this->sprawl = $sprawl;

        return $this;
    }

    public function getActualDepth(): ?int
    {
        return $this->actualDepth;
    }

    public function setActualDepth(int $actualDepth): static
    {
        $this->actualDepth = $actualDepth;

        return $this;
    }

    public function getRelativeDepth(): ?int
    {
        return $this->relativeDepth;
    }

    public function setRelativeDepth(int $relativeDepth): static
    {
        $this->relativeDepth = $relativeDepth;

        return $this;
    }

    public function getPits(): ?int
    {
        return $this->pits;
    }

    public function setPits(int $pits): static
    {
        $this->pits = $pits;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setFloor($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getFloor() === $this) {
                $room->setFloor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getExits(): Collection
    {
        return $this->exits;
    }

    public function addExit(Room $exit): static
    {
        if (!$this->exits->contains($exit)) {
            $this->exits->add($exit);
            $exit->setLeavesFloor($this);
        }

        return $this;
    }

    public function removeExit(Room $exit): static
    {
        if ($this->exits->removeElement($exit)) {
            // set the owning side to null (unless already changed)
            if ($exit->getLeavesFloor() === $this) {
                $exit->setLeavesFloor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getEntrances(): Collection
    {
        return $this->entrances;
    }

    public function addEntrance(Room $entrance): static
    {
        if (!$this->entrances->contains($entrance)) {
            $this->entrances->add($entrance);
            $entrance->setEntersFloor($this);
        }

        return $this;
    }

    public function removeEntrance(Room $entrance): static
    {
        if ($this->entrances->removeElement($entrance)) {
            // set the owning side to null (unless already changed)
            if ($entrance->getEntersFloor() === $this) {
                $entrance->setEntersFloor(null);
            }
        }

        return $this;
    }

    public function getDungeon(): ?Dungeon
    {
        return $this->dungeon;
    }

    public function setDungeon(?Dungeon $dungeon): static
    {
        $this->dungeon = $dungeon;

        return $this;
    }
}
