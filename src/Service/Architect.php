<?php

namespace App\Service;

use App\Entity\Dungeon;
use App\Entity\Floor;
use App\Entity\Room;
use App\Entity\RoomType;
use App\Entity\Transit;
use App\Entity\TransitType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Contracts\Translation\TranslatorInterface;

class Architect {
	protected EntityManagerInterface $em;
	protected TranslatorInterface $trans;
	private ?array $allRoomTypes = null;
	private ?Room $foundPit = null;
	public int $SPRAWL = 25;
	public int $PEACEMOD = 25;
	public int $TELEPORTMOD = 10;
	public float $PITRANGE = 0.5;

	# These chances are cumulative, with exceptions. Think of them as weights.
	# Most common rooms should be first, less common rooms later.
	# These failover into the next item.
	# Same with path counts.
	public array $ROOMCHANCES = [
		'normal' => 100,
		'stairs' => 105,
		'deep pit' => 106,
		'pit bottom' => 107,
	];
	public array $EXITROOMS = [
		'stairs',
		'deep pit'
	];
	public int $EXITCAP = 3;
	public int $BYPASSCHANCE = 1;
	public array $MAINPATHCHANCES = [
		2 => 100,
		3 => 125,
		4 => 135,
		5 => 140,
		6 => 143,
		7 => 145,
		8 => 146,
	];
	public int $MAINPATHMAX = 225;
	public array $SIDEPATHCHANCES = [
		1 => 50,
		2 => 65,
		3 => 70,
		4 => 73,
		5 => 75,
		6 => 76,
	];
	public int $SIDEPATHMAX = 106;

	public function __construct(EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->em = $em;
		$this->trans = $trans;
	}

	public function buildFloor(Dungeon $dungeon, int $aDepth, int $rDepth): Floor {
		$max = (int)(1+($aDepth/5))*$this->SPRAWL;
		$min = (int)(1+($aDepth/100))*$this->SPRAWL;
		$floorSprawl = rand($min, $max);

		$floor = new Floor();
		$this->em->persist($floor);
		$floor->setVisits(0);
		$floor->setSprawl($floorSprawl);
		$floor->setDungeon($dungeon);
		$floor->setActualDepth($aDepth);
		$floor->setRelativeDepth($rDepth);
		$floor->setFinalized(false); # Always false to start.
		$floor->setPits(0);
		if ($aDepth % $this->PEACEMOD === 0) {
			$floor->setType('peaceful');
		} else {
			$floor->setType('normal');
		}
		$this->em->flush();

		$range = 0;
		$exits = 0;
		$unfinishedRooms = new ArrayCollection();
		$last = null;
		$teleporter = false;
		if ($aDepth % $this->TELEPORTMOD === 0) {
			$teleporter = true;
		}
		# Build the main path.
		while ($range <= $floorSprawl) {
			$room = new Room;
			$this->em->persist($room);
			$room->setFloor($floor);
			$room->setVisits(0);
			if ($range === 1 && $teleporter) {
				$type = $this->findRoomTypeNamed('teleporter');
			} else {
				$type = $this->pickRoomType($floor, $range, $exits);
				if ($type->getName() === 'stairs') {
					$exits++;
				}
			}
			$room->setType($type);
			$room->setRange($range);
			$room->setDungeonExit(false); # Default value.
			if ($range === 0) {
				# This only hits on the very first room.
				$room->setPathRoll(0);
				if ($aDepth === 1) {
					# Sets the very first room of the first floor as the dungeon exit.
					$room->setDungeonExit(true);
				} else {
					echo "looking for higher floor\n";
					# We need to find the floor above us for this stair to connect to.
					$room->setEntersToFloor($floor);
					$farthest = null;
					$targetFloor = null;
					$possibleRooms = $this->em->createQuery(
						"SELECT r FROM App:Room r JOIN r.floor f WHERE r.type = :type AND f.actualDepth < :myDepth"
					)->setParameters([
						'type'=>$this->findRoomTypeNamed('stairs'),
						'myDepth'=>$floor->getActualDepth()
					])->getResult();
					foreach ($possibleRooms as $potential) {
						if (!$farthest || $potential->getRange() > $farthest->getRange()) {
							echo "correct range or no farthest yet";
							$valid = true;
							foreach ($potential->getExits() as $transit) {
								if ($transit->getDirection() === "D") {
									$valid = false;
								}
							}
							if ($potential->getRange() !== 0 && $valid) {
								$farthest = $potential;
								$targetFloor = $potential->getFloor();
								echo "found\n";
							}
						}
					}
					if ($farthest) {
						$this->createTransitPair($farthest, $room, $dungeon, 'stairs');
						$floor->addExit($farthest);
						$targetFloor->addEntrance($room);
					}
					/*
					foreach ($above as $higherFloor) {
						echo "floor ".$higherFloor->getId()."\n";
						echo get_class($higherFloor)."\n";
						$upperExits = 1; # All floors start with an exit that is either The Exit or is already linked.
						foreach ($higherFloor->getRooms() as $potential) {
							echo "evaluating room ".$potential->getId()."\n";

						}

						$neededExits = 0;
						$hasExits = 0;
						foreach ($higherFloor->getRooms() as $each) {
							if ($each->getType()->getName() === 'stairs') {
								$neededExits++;
								foreach ($each->getTransits() as $path) {
									if ($path->getDirection() === "D") {
										$hasExits++;
									}
								}
							} elseif ($each->getType()->getName() === 'deep pit') {
								$neededExits++;
								foreach ($each->getTransits() as $path) {
									if ($path->getDirection() === "D") {
										$hasExits++;
									}
								}
							}
						}
						if ($neededExits && $hasExits && $neededExits == $hasExits) {
							$higherFloor->setFinalized(true);
						}
						if ($farthest && $targetFloor) {
							break;
						}
					}*/
					if (!$farthest) {
						throw new LogicException("Unable to locate stairs for basic floor to floor connection!");
					}
				}
			} else {
				# Logic for other rooms.
				if ($type->getName() === 'stairs') {
					$room->setPathRoll(1);
				} else {
					$pathRoll = $this->rollPath($this->MAINPATHCHANCES, $this->MAINPATHMAX);
					$room->setPathRoll($pathRoll);
					if ($pathRoll > 2) {
						$unfinishedRooms->add($room);
					}
				}
			}
			$this->em->flush();
			if ($last) {
				$this->createTransitPair($last, $room, $dungeon);
			}
			if ($this->foundPit) {
				# Deep pits are one way transits from the Pit to the Bottom!
				$this->createTransit($this->foundPit, $room, $dungeon, 'deep pit');
				$this->foundPit = null;
			}
			$last = $room;
			$this->em->flush();
			$range++;
		}
		# Build the side paths.
		while ($unfinishedRooms->count() !== 0) {
			foreach ($unfinishedRooms as $unfinished) {
				/** @var Room $unfinished */
				$neededPaths = $unfinished->getPathRoll();
				$paths = $unfinished->getExits()->count();
				echo "Room ".$unfinished->getId()." has $paths and needs $neededPaths\n";
				$range = $unfinished->getRange()+1; #We're connecting this room to the unfinished one.

				$room = new Room;
				$this->em->persist($room);
				$room->setFloor($floor);
				$room->setVisits(0);
				$room->setRange($range);
				$room->setDungeonExit(false); # Default value.
				$type = $this->pickRoomType($floor, $range, $exits);
				if ($type->getName() === 'stairs' && $range !== 0) {
					$exits++;
				}
				$room->setType($type);
				$pathRoll = $this->rollPath($this->SIDEPATHCHANCES, $this->SIDEPATHMAX);
				echo "SidePathRoll: $pathRoll\n";
				$room->setPathRoll($pathRoll);
				if ($pathRoll > 2) {
					$unfinishedRooms->add($room);
				}
				$this->em->flush();

				if ($this->foundPit) {
					$this->createTransit($this->foundPit, $room, $dungeon, 'deep pit');
					$this->foundPit = null;
				} else {
					$this->createTransitPair($unfinished, $room, $dungeon);
				}
				$this->em->flush();
				if ($paths >= $neededPaths) {
					# Room done, remove from pile.
					$unfinishedRooms->removeElement($unfinished);
				}
			}
		}
		$bypassRoll = rand(1,100);
		if ($bypassRoll <= $this->BYPASSCHANCE) {
			$above = $this->em->createQuery(
				"SELECT f FROM App:Floor f WHERE finalized=false AND f.actualDepth < :myDepth ORDER BY f.actualDepth ASC"
			)->setParameters(
				['myDepth'=>$floor->getActualDepth()]
			)->getResult();
			if (count($above) > 0) {
				foreach ($above as $higher) {
					foreach ($higher->getExits() as $potential) {
						if ($potential->getType()->getName() === 'stairs' &&  $potential->getTransits()->count() === 1) {
							$room = new Room;
							$this->em->persist($room);
							$room->setFloor($floor);
							$room->setDungeonExit(false);
							$room->setType($this->findRoomTypeNamed('stairs'));
							$room->setPathRoll(0);
							$potential->setEntersToFloor($higher);
							$room->setLeavesToFloor($floor);
							$this->createTransitPair($potential, $room, $dungeon, 'stairs');
							break;
						}
					}
				}
			}
		}
		$this->em->flush();
		echo "Floor completed!\n";
		return $floor;
	}

	public function createTransitPair($a, $b, $dungeon, $type = 'normal'): void {
		$this->createTransit($a, $b, $dungeon, $type);
		$this->createTransit($b, $a, $dungeon, $type);
	}

	public function createTransit(Room $from, Room $to, Dungeon $dungeon, $type = 'normal'): void {
		$trans = new Transit();
		$this->em->persist($trans);
		$trans->setDungeon($dungeon);
		$trans->setType($this->em->getRepository(TransitType::class)->findOneBy(['name'=>$type]));
		$trans->setFromRoom($from);
		$from->addExit($trans);
		$trans->setToRoom($to);
		$to->addEntrance($trans);
		$trans->setTransits(0);
		if ($type === 'normal') {
			$trans->setDirection($from->findAvailableDirection());
		} elseif ($type === 'stairs') {
			if ($from->getFloor()->getActualDepth() < $to->getFloor()->getActualDepth()) {
				$trans->setDirection("D");
			} else {
				$trans->setDirection("U");
			}
		} elseif ($type === 'deep pit') {
			$trans->setDirection("D");
		}
	}

	public function rollPath(array $chances, int $max): int {
		$roll = rand(1,$max);
		foreach ($chances as $count=>$chance) {
			if ($roll <= $chance) {
				return $count;
			}
		}
		# We should never get here, but just in case...
		return 1;
	}

	/**
	 * Picks a room type from the available types of room for this floor.
	 * @param Floor $floor
	 * @param int   $range
	 * @param int   $exits
	 *
	 * @return RoomType
	 */
	public function pickRoomType(Floor $floor, int $range, int $exits): RoomType {
		if ($this->allRoomTypes === null) {
			$this->allRoomTypes = $this->em->createQuery(
				'SELECT t FROM App:RoomType t WHERE t.minDepth <= :depth'
			)->setParameters([
				'depth'=>$floor->getActualDepth()
			])->getResult();
		}
		if ($range === 0 || ($range >= $floor->getSprawl() && $exits === 1)) {
			/*
			 * If range is 0, the start of a floor,
			 * or range is greater than the floor sprawl (the other side of a floor)
			 * and we only have one exit, we need stairs!
			 */
			return $this->findRoomTypeNamed('stairs');
		}
		$roll = rand(1,$this->ROOMCHANCES[array_key_last($this->ROOMCHANCES)]);
		$max = count($this->ROOMCHANCES);

		$result = false;
		$rounds = 1;
		$rerolls = 0;
		while (!$result) {
			if ($rounds > $max) {
				if ($rerolls > 2) {
					throw new LogicException("Unable to pickRoomType, never finding valid pick!");
				}
				# Something weird is going on. Re-roll.
				$roll = rand(1,$this->ROOMCHANCES[array_key_last($this->ROOMCHANCES)]);
				$rerolls++;
				$rounds = 1;
			}
			foreach ($this->ROOMCHANCES as $name=>$chance) {
				if ($exits <= $this->EXITCAP && in_array($name, $this->EXITROOMS)) {
					break;
				}
				if ($roll <= $chance) {
					echo "Roll $roll vs $chance, name $name\n";
					$result = $this->validateRoomPick($name, $floor, $range);
				}
				$rounds++;
			}
		}
		return $this->findRoomTypeNamed($result);
	}

	/** @noinspection PhpMissingBreakStatementInspection */
	public function validateRoomPick(string $name, Floor $floor, $range): bool|string {
		switch ($name) {
			case 'normal':
				return $name;
			case 'stairs':
				if ($range >= $floor->getSprawl()) {
					return $name;
				}
			case 'deep pit':
				if ($range/$floor->getSprawl() >= $this->PITRANGE) {
					return $name;
				}
			case 'pit bottom':
				$found = $this->findDeepPit($floor);
				if ($found) {
					$this->foundPit = $found;
					return $name;
				}
		}
		return false;
	}

	public function findDeepPit(Floor $floor): false|Room {
		if ($floor->getActualDepth() === 1) {
			return false;
		}
		$possibilities = $this->em->createQuery("SELECT r FROM App:Room r JOIN r.type t WHERE t.name = 'deep pit'")->getResult();
		$count = count($possibilities);
		$return = null;
		if ($count == 0) {
			$return = false;
		} elseif ($count == 1) {
			$return = $possibilities[0];
		} else {
			$rand = rand(1,$count);
			$i = 1;
			foreach ($possibilities as $each) {
				if ($i == $rand) {
					$return = $each;
				}
				$i++;
			}
		}
		return $return;
	}

	public function findRoomTypeNamed(string $name) {
		if ($this->allRoomTypes === null) {
			$this->allRoomTypes = $this->em->getRepository(RoomType::class)->findAll();
		}
		foreach ($this->allRoomTypes as $type) {
			if ($type->getName() === $name) {
				return $type;
			}
		}
		throw new LogicException("Unknown room requested in DungeonMaster->findRoomTypeNamed!");
	}
}
