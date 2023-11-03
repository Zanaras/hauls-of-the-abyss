<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Dungeon;
use App\Entity\Journal;
use App\Entity\Room;
use App\Entity\RoomType;
use App\Entity\Skill;
use App\Entity\SkillType;
use App\Entity\Transit;
use App\Entity\TransitType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DungeonMaster {

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
	const MAINPATHCHANCES = [
		2 => 100,
		3 => 110,
		4 => 115,
		5 => 116,
	];
	const SIDEPATHCHANCES = [
		1 => 25,
		2 => 75,
		3 => 85,
		4 => 86,
	];

	public function __construct(EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->em = $em;
		$this->trans = $trans;
	}

	public function checkDungeon(Dungeon $dungeon) {
		$floors = $this->em->createQuery('SELECT f FROM App:Floor WHERE dungeon = :dungeon ORDER BY actualDepth DESC')->setParameters(['dungeon'=>$dungeon])->getResult();
		$floorCount = $dungeon->getFloors()->count();
		if ($floorCount === 0) {
			$this->buildFloor($dungeon, 1, 1);
		} else {
			 foreach ($floors as $floor) {
				 if ($floor->getCharacters()->count() > 0) {
					 continue;
				 } else {
					 $this->buildFloor($dungeon, $floor->getActualDepth()+1, $floor->getRelativeDepth()+1);
				 }
			 }
		}
	}

	public function buildFloor(Dungeon $dungeon, int $aDepth, int $rDepth) {
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
		$floor->setPits(0);
		if ($aDepth % self::PEACEMOD === 0) {
			$floor->setType('peaceful');
		} else {
			$floor->setType('normal');
		}
		$this->em->flush();

		$i = 0;
		$exits = 0;
		$unfinishedRooms = new ArrayCollection();
		$last = null;
		while ($i < $floorSprawl) {
			$room = new Room;
			$this->em->persist($room);
			$room->setFloor($floor);
			$type = $this->pickRoomType($floor, $i);
			$room->setType($type);
			if ($i === 0) {
				$room->setPathRoll(0);
				$room->setEntersFloor($floor);
				if ($aDepth === 1) {
					$room->setDungeonExit(true);
				} else {
					$room->setDungeonExit(false);
				}
			} else {
				$room->setPathRoll($this->rollPath());
			}
			$this->em->flush();
			if ($last) {
				$this->createTransitPair($last, $room, $dungeon);
			}
			if ($this->foundPit) {
				$this->createTransit($this->foundPit, $room, $dungeon);
			}
		}
	}

	public function createTransitPair($a, $b, $dungeon, $type = 'normal') {
		$this->createTransit($a, $b, $dungeon, $type);
		$this->createTransit($b, $a, $dungeon, $type);
	}

	public function createTransit($from, $to, $dungeon, $type = 'normal') {
		$trans = new Transit();
		$this->em->persist($trans);
		$trans->setDungeon($dungeon);
		$trans->setType($this->em->getRepository(TransitType::class)->findOneBy(['name'=>$type]));
		$trans->setFromRoom($from);
		$trans->setToRoom($to);
	}

	public function rollPath(): int {
		$roll = rand(1,self::MAINPATHCHANCES[-1]);
		foreach (self::MAINPATHCHANCES as $count=> $chance) {
			if ($chance <= $roll) {
				return $count;
			}
		}
		# We should never get here, but just in case...
		return 1;
	}

	public function pickRoomType(Floor $floor, int $range, int $exits): RoomType {
		if ($this->allRoomTypes !== null) {
			$this->allRoomTypes = $this->em->getRepository(RoomType::class)->findAll();
		}
		if ($range === 0 || ($range >= $floor->getSprawl() && $exits === 0)) {
			# Floor starter or ender!
			return $this->findRoomTypeNamed('stairs');
		}
		$roll = rand(1,self::ROOMCHANCES[-1]);
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
				$roll = rand(1,self::ROOMCHANCES[-1]);
				$rerolls++;
				$rounds = 1;
			}
			foreach (self::ROOMCHANCES as $name=>$chance) {
				if ($roll <= $chance) {
					$result = $this->validateRoomPick($name, $floor, $range);
				}
				$rounds++;
			}
		}
		return $this->findRoomTypeNamed($result);
	}

	public function validateRoomPick(string $name, Floor $floor, $range) {
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
		if ($count == 0) {
			return false;
		} elseif ($count == 1) {
			return $possibilities[0];
		} else {
			$rand = rand(1,$count);
			$i = 1;
			foreach ($possibilities as $each) {
				if ($i == $rand) {
					return $each;
				}
				$i++;
			}
		}
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
		throw new \LogicException("Unknown room requested in DungeonMaster->findRoomTypeNamed!");
	}

	public function characterAttackMob(Character $char, $target) {
		#TODO: Logic n stuff.
	}

	public function calculateEnergyCost(Character $char, string $action) {
		switch ($action) {
			case 'move':
				$skill = $this->fetchSkill($char, 'dungeoneering');
				# 10000 is the highest skill we'll worry about, so subtract the evaluated skill from that, then divide by 10000.
				# This reduces it to a 5th place decimal: 0.#####.
				$cost = (100000-$skill->evaluate())/100000;
				# Return the result of lower value of 1 and the result higher value of $cost and 0.1.
				# This ensures the cost never goes below 0.1, but never costs more than 1.
				return min(1, max($cost, 0.1));
		}
	}

	public function fetchSkill(Character $char, SkillType|string $skillType): Skill {
		$isType = false;
		if ($skillType instanceof SkillType) {
			$isType = true;
			if ($skill = $char->findSkill($skillType)) {
				return $skill;
			}
		} else {
			if ($skill = $char->findSkillbyName($skillType)) {
				return $skill;
			}
		}
		if (!$isType) {
			$skillType = $this->findSkillTypeByName($skillType);
			if (!$skillType) {
				throw new \LogicException("Function DungeonMaster::fetchSkill asked to fetch SkillType that doesn't exist in database!");
			}
		}
		# If we're here, this skill doesn't exist, and we need it to.
		return $this->newSkill($char, $skillType);
	}

	public function findSkillTypeByName(string $name): SkillType|false {
		return $this->em->getRepository(SkillType::class)->findOneBy(['name'=>$name])?:false;
	}

	public function moveCharacter(Character $char, Transit $where): void {
		$oldRoom = $char->getRoom();
		$newRoom = $where->getToRoom();
		$char->setRoom($newRoom);
		$char->setLastRoom($oldRoom);
		$newRoom->setVisits($newRoom->getVisits()+1);
		$where->setTransits($where->getTransits()+1);
		$char->setEnergy($char->getEnergy()-$this->calculateEnergyCost($char, 'move'));
		$trained = $this->trainSkill($char, $this->findSkillTypeByName('dungeoneering'), 1);
		$trained?:$this->em->flush();
	}

	public function newSkill(Character $char, SkillType $type): Skill {
		$skill = new Skill;
		$this->em->persist($skill);
		$skill->setCharacter($char);
		$skill->setType($type);
		$skill->setCategory($type->getCategory());
		$skill->setPractice(1);
		$skill->setTheory(1);
		$skill->setPracticeHigh(1);
		$skill->setTheoryHigh(1);
		$skill->setUpdated(new \DateTime('now'));
		$this->em->flush();
		return $skill;
	}

	public function trainSkill(Character $char, SkillType $type=null, $pract = 0, $theory = 0): bool {
		if (!$type) {
			# Not all weapons have skills, this just catches those.
			return false;
		}
		$query = $this->em->createQuery('SELECT s FROM App:Skill s WHERE s.character = :me AND s.type = :type ORDER BY s.id ASC')->setParameters(['me'=>$char, 'type'=>$type])->setMaxResults(1);
		$training = $query->getResult();
		if ($pract && $pract < 1) {
			$pract = 1;
		} elseif ($pract) {
			$pract = round($pract);
		}
		if ($theory && $theory < 1) {
			$theory = 1;
		} elseif ($theory) {
			$theory = round($theory);
		}
		if (!$training) {
			echo 'making new skill - ';
			$training = new Skill();
			$this->em->persist($training);
			$training->setCharacter($char);
			$training->setType($type);
			$training->setCategory($type->getCategory());
			$training->setPractice($pract);
			$training->setTheory($theory);
			$training->setPracticeHigh($pract);
			$training->setTheoryHigh($theory);
		} else {
			$training = $training[0];
			echo 'updating skill '.$training->getId().' - ';
			if ($pract) {
				$newPract = $training->getPractice() + $pract;
				$training->setPractice($newPract);
				if ($newPract > $training->getPracticeHigh()) {
					$training->setPracticeHigh($newPract);
				}
			}
			if ($theory) {
				$newTheory = $training->getTheory() + $theory;
				$training->getTheory($newTheory);
				if ($newTheory > $training->getTheoryHigh()) {
					$training->setTheoryHigh($newTheory);
				}
			}
		}
		$training->setUpdated(new \DateTime('now'));
		$this->em->flush();
		return true;
	}

}
