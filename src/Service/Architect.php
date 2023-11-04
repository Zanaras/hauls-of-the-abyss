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
	const SPRAWL = 25;
	const PEACEMOD = 25;
	const TELEPORTMOD = 10;
	const PITRANGE = 0.5;

	# These chances are cumulative, with exceptions. Think of them as weights.
	# Most common rooms should be first, less common rooms later.
	# These failover into the next item.
	# Same with path counts.
	const ROOMCHANCES = [
		'normal' => 100,
		'stairs' => 105,
		'deep pit' => 106,
		'pit bottom' => 107,
	];
	const EXITROOMS = [
		'stairs',
		'deep pit'
	];
	const EXITCAP = 3;
	const BYPASSCHANCE = 1;
	const MAINPATHCHANCES = [
		2 => 100,
		3 => 150,
		4 => 175,
		5 => 195,
		6 => 210,
		7 => 220,
		8 => 225,
	];
	const SIDEPATHCHANCES = [
		1 => 50,
		2 => 75,
		3 => 90,
		4 => 100,
		5 => 105,
		6 => 106,
	];

	public function __construct(EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->em = $em;
		$this->trans = $trans;
	}

	public function buildFloor(Dungeon $dungeon, int $aDepth, int $rDepth): Floor {
		$max = (int)(1+($aDepth/5))*self::SPRAWL;
		$min = (int)(1+($aDepth/100))*self::SPRAWL;
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
		if ($aDepth % self::PEACEMOD === 0) {
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
		if ($aDepth % self::TELEPORTMOD === 0) {
			$teleporter = true;
		}
		# Build the main path.
		while ($range < $floorSprawl) {
			$room = new Room;
			$this->em->persist($room);
			$room->setFloor($floor);
			if ($range === 1 && $teleporter) {
				$type = $this->findRoomTypeNamed('teleporter');
			} else {
				$type = $this->pickRoomType($floor, $range, $exits);
				if ($type->getName() === 'stairs' && $range !== 0) {
					$exits++;
				}
			}
			$room->setType($type);
			if ($range === 0) {
				# This only hits on the very first room.
				$room->setPathRoll(0);
				if ($aDepth === 1) {
					# Sets the very first room of the first floor as the dungeon exit.
					$room->setDungeonExit(true);
				} else {
					# Sets every other first room of other floors as not.
					$room->setDungeonExit(false);
					# We need to find the floor above us for this stair to connect to.
					$above = $this->em->createQuery(
						"SELECT f FROM App:Floor f WHERE finalized=false AND actualDepth < :myDepth"
					)->setParameters(
						['myDepth'=>$floor->getActualDepth()-1]
					)->getResult();
					$room->setEntersToFloor($floor);
					foreach ($above as $higher) {
						foreach ($higher->getExits() as $potential) {
							if ($potential->getTransits()->count() === 1) {
								$this->createTransitPair($potential, $room, $dungeon, 'stairs');
								$room->setLeavesToFloor($higher);
								$potential->setLeavesToFloor($floor);
								break;
							}
						}
					}
				}
			} else {
				# Logic for other rooms.
				$pathRoll = $this->rollPath(self::MAINPATHCHANCES);
				$room->setPathRoll($pathRoll);
				if ($pathRoll > 2) {
					$unfinishedRooms->add($room);
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
				$neededPaths = $unfinished->getPathRoll();
				$paths = 1; # We got here somehow.
				$range = $unfinished->getRange()+1; #We're connecting this room to the unfinished one.

				$room = new Room;
				$this->em->persist($room);
				$room->setFloor($floor);
				$type = $this->pickRoomType($floor, $range, $exits);
				if ($type->getName() === 'stairs' && $range !== 0) {
					$exits++;
				}
				$room->setType($type);
				$pathRoll = $this->rollPath(self::SIDEPATHCHANCES);
				$room->setPathRoll($pathRoll);
				if ($pathRoll > 2) {
					$unfinishedRooms->add($room);
				}
				$this->em->flush();

				if ($last) {
					$this->createTransitPair($last, $room, $dungeon);
				}
				if ($this->foundPit) {
					$this->createTransit($this->foundPit, $room, $dungeon, 'deep pit');
					$this->foundPit = null;
				}
				$this->em->flush();
				if ($paths == $neededPaths) {
					# Room done, remove from pile.
					$unfinishedRooms->remove($unfinished);
				}
			}
		}
		$bypassRoll = rand(1,100);
		if ($bypassRoll <= self::BYPASSCHANCE) {
			$above = $this->em->createQuery(
				"SELECT f FROM App:Floor f WHERE finalized=false AND actualDepth < :myDepth ORDER BY actualDepth ASC"
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
							$room->setEntersToFloor($higher);
							$room->setLeavesToFloor($floor);
							$this->createTransitPair($potential, $room, $dungeon, 'stairs');
							break;
						}
					}
				}
			}
		}
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
		$trans->setToRoom($to);
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

	public function rollPath(array $chances): int {
		$roll = rand(1,$chances[array_key_last($chances)]);
		foreach ($chances as $count=> $chance) {
			if ($chance <= $roll) {
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
		if ($this->allRoomTypes !== null) {
			$this->allRoomTypes = $this->em->createQuery(
				'SELECT t FROM App:RoomType t WHERE minDepth <= :depth'
			)->setParameters([
				'depth'=>$floor->getActualDepth()
			])->getResult();
		}
		if ($range === 0 || ($range >= $floor->getSprawl() && $exits === 0)) {
			# Floor starter or ender!
			return $this->findRoomTypeNamed('stairs');
		}
		$roll = rand(1,self::ROOMCHANCES[array_key_last(self::ROOMCHANCES)]);
		$max = count(self::ROOMCHANCES);

		$result = false;
		$rounds = 1;
		$rerolls = 0;
		while (!$result) {
			if ($rounds > $max) {
				if ($rerolls > 2) {
					throw new LogicException("Unable to pickRoomType, never finding valid pick!");
				}
				# Something weird is going on. Re-roll.
				$roll = rand(1,self::ROOMCHANCES[array_key_last(self::ROOMCHANCES)]);
				$rerolls++;
				$rounds = 1;
			}
			foreach (self::ROOMCHANCES as $name=>$chance) {
				if ($exits <= self::EXITCAP && in_array($name, self::EXITROOMS)) {
					break;
				}
				if ($roll <= $chance) {
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
				if ($range/$floor->getSprawl() >= self::PITRANGE) {
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
		if ($this->allRoomTypes !== null) {
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
