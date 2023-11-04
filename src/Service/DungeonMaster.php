<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Floor;
use App\Entity\Monster;
use App\Entity\Dungeon;
use App\Entity\Room;
use App\Entity\Skill;
use App\Entity\SkillType;
use App\Entity\Transit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DungeonMaster {
	private Architect $architect;
	private EntityManagerInterface $em;
	private TranslatorInterface $trans;
	const PREBUILD = 2;
	const BASEPOPRATE = 0.85;
	const POPDEPTHMIN = 0.69;
	const POPDEPTHMAX = 2.71;

	public function __construct(Architect $architect, EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->architect = $architect;
		$this->em = $em;
		$this->trans = $trans;
	}

	public function checkDungeon(Dungeon $dungeon): void {
		$floors = $this->em->createQuery(
			'SELECT f FROM App:Floor WHERE dungeon = :dungeon ORDER BY actualDepth DESC'
		)->setParameters(['dungeon'=>$dungeon])->getResult();
		$floorCount = $dungeon->getFloors()->count();
		$lowest = 0;
		$lowestOccupied = null;

		# Builder loop, commence!
		if ($floorCount === 0) {
			$floors = [];
			$count = 0;
			while ($count < self::PREBUILD) {
				# Basically, build the PREBUILD number of floors.
				$count++;
				$floors[] = $this->architect->buildFloor($dungeon, $count, $count);
			}
		} else {
			 foreach ($floors as $floor) {
				 if (!$lowest) {
					 # Yay PHP, 0 evalutates to false, so this only gets hit the first time.
					 $lowest = $floor->getActualDepth();
				 }
				 if ($floor->getVisits() < 1) {
					 # Don't care about empty floors.
					 continue;
				 } else {
					 $lowestOccupied = $floor;
					 break;
				 }
			 }
			 $difference = $lowest-$lowestOccupied->getActualDepth();
			 $count = 0;
			 if ($difference < self::PREBUILD) {
				 while ($count < self::PREBUILD) {
					 $count++;
					 $floors[] = $this->architect->buildFloor($dungeon, $lowestOccupied->getActualDepth()+1, $lowestOccupied->getRelativeDepth()+1);
				 }
			 }
		}

		# Spawner loop, commence!
		foreach ($floors as $floor) {
			$this->checkFloor($floor);
		}

	}

	public function checkFloor(Floor $floor): void {
		$rooms = $floor->getRooms();
		$roomCount = $rooms->count();
		$popRate = $floor->getPopRate();
		if (!$popRate) {
			$popRate = $roomCount*self::BASEPOPRATE;
			$floor->setPopRate($popRate);
		}
		$monsters = $floor->getMonsters()->count();
		$spawners = $floor->fetchSpawnableRooms();
		$currentRate = $monsters/$popRate;
		while ($currentRate < $popRate) {
			$monster = $this->spawnMob($floor->getActualDepth());
			$where = $this->findMonsterSpawnRoom($monster, $spawners);
			if ($where) {
				$monster->setRoom($where);
				$monster->setFloor($floor);
			}
		}
	}

	/**
	 * Returns a room from a collection of rooms that a particular monster can spawn in.
	 * @param Monster         $monster
	 * @param ArrayCollection $rooms
	 *
	 * @return Room|false
	 */
	public function findMonsterSpawnRoom(Monster $monster, ArrayCollection $rooms): Room|false {
		$total = $rooms->count();
		$random = rand(1, $total);
		$count = 1;
		/**
		 * This may look like it runs throw twice at a glance, but it skips up to all of the first pass and may skip nearly all of the second pass.
		 * Basically, we're just ensuring we start on a random array value.
		 */
		foreach ($rooms as $room) {
			if ($count < $random) {
				$count++;
				continue;
			}
			if ($this->validateMonsterSpawn($monster, $room)) {
				return $room;
			}
			$count++;
		}
		$max = $random*2;
		foreach ($rooms as $room) {
			if ($count > $max) {
				break;
			}
			if ($this->validateMonsterSpawn($monster, $room)) {
				return $room;
			}
		}
		return false;
	}

	/**
	 * Validates that the room selected for a monster is valid for that monster.
	 * Returns true/false.
	 * @param Monster $monster
	 * @param Room    $room
	 *
	 * @return bool
	 */
	public function validateMonsterSpawn(Monster $monster, Room $room): bool {
		#TODO: Actually add some validation rooms, so we can keep monsters out of certain rooms, or stop over population, or whatever we want.
		return true;
	}

	public function characterAttackMob(Character $char, Monster $target) : void {
		#TODO: Logic n stuff.
        # roll for hit on mob
        # if success && mob == neutral
        # then set room agro (??)
        # if success, roll for DMG
        # set rolled DMG multiplier(???)
        # calculate damage based on player stats
        #
        # apply DMG to Mob (account for resistances?)
        # if Mob health < 0
        # set Mob = dead
        #
	}

    public function mobAttackCharacter(Monster $mob, Character $target) : void {
        #TODO: implement logic
        # roll for hit on player
        #
        # if success, roll for DMG
        # set multiplier(???)
        # calculate damage based on mob stats
        #
        # apply DMG to character (account for resistances?)
        # if Player health < 0
        # kill/respawn player(???)
        # kick out of dungoen(???)
        # ?????????
    }

    public function spawnMob(int $depth, array $floorTypes = [], array $roomTypes = []) : Monster {
        $monster = new Monster;
        #
        #TODO: logic
        #
        #if $mobType == null or empty
        #roll for random mob
        #
        #calculate and set mob health
        #
        return $monster;
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
			default:
				return 1;
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
