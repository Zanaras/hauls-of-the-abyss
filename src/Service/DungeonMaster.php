<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Dungeon;
use App\Entity\Floor;
use App\Entity\Monster;
use App\Entity\MonsterType;
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

	#TODO: Rework these into AppSettings.
	private int $prebuild = 1;
	private float $basePopRate = 0.85;
	private float $popDepthMin = 0.69;
	private float $popDepthMax = 2.71;
	private float $baseEnergy = 300;
	private float $baseHealth = 100;


	public function __construct(Architect $architect, EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->architect = $architect;
		$this->em = $em;
		$this->trans = $trans;
	}

	public function calculateMaxEnergy(Monster|Character $thing, Floor $floor = null): float {
		$mod = match ($thing->getRace()->getSize()) {
			'tiny' => 1.5,
			'small' => 1.25,
			'large' => 0.75,
			'huge' => 0.5,
			default => 1,
		};

		#TODO: Do we want deeper monster spawns to have more energy?
		$fMod = 1;
		if ($floor && $floor->getActualDepth() > 10) {
			$fMod = $floor->getActualDepth()/10;
		}
		return $this->baseEnergy * $thing->getRace()->getEndurance() * $mod;
	}

	public function calculateMaxHealth(Monster|Character $thing, Floor $floor = null): float {
		$mod = match ($thing->getRace()->getSize()) {
			'tiny' => 0.5,
			'small' => 0.75,
			'large' => 1.25,
			'huge' => 1.5,
			default => 1,
		};
		if ($floor) {
			$fMod = min($floor->getActualDepth()/10, 1);
		} else {
			$fMod = 1;
		}
		return $this->baseHealth * $thing->getRace()->getConstitution()*$mod*$fMod;
	}

	public function checkDungeon(Dungeon $dungeon): void {
		$floors = $this->em->createQuery('SELECT f FROM App:Floor f WHERE f.dungeon = :dungeon ORDER BY f.actualDepth DESC')->setParameters(['dungeon' => $dungeon])->getResult();
		$floorCount = $dungeon->getFloors()->count();
		echo "$floorCount floors detected...\n";
		$lowest = 0;
		$lowestOccupied = null;

		# Builder loop, commence!
		if ($floorCount === 0) {
			echo "Entered 0 floor code...\n";
			$floors = [];
			$count = 0;
			while ($count < $this->prebuild) {
				# Basically, build the PREBUILD number of floors.
				$count++;
				$this->architect->buildFloor($dungeon, $count, $count);
			}
		} else {
			echo "Finding lowest occupied floor...\n";
			foreach ($floors as $floor) {
				if (!$lowest) {
					$lowest = $floor->getActualDepth();
				}
				if ($floor->getVisits() < 1) {
					# Don't care about empty floors.
					continue;
				} else {
					if (!$lowestOccupied) {
						# Yay PHP, 0 evalutates to false, so this only gets hit the first time.
						$lowestOccupied = $floor;
					}
					break;
				}
			}
			if ($lowestOccupied) {
				$id = $lowestOccupied->getId();
				$actual = $lowestOccupied->getActualDepth();
				echo "Lowest floor is $id at depth $actual...\n";
				$difference = $lowest - $lowestOccupied->getActualDepth();
				echo "Lowest floor difference from occupied floor is $difference...\n";
				$count = 0;
				if ($difference < 1) {
					while ($count < $this->prebuild) {
						echo "Building a floor!\n";
						$count++;
						$this->architect->buildFloor($dungeon, $actual + $count, $lowestOccupied->getRelativeDepth() + $count);
					}
				}
			}
		}
		/*
		 * Clearing the doctrine cache and starting fresh, reloading everything to ensure we are looking at the right stuff.
		 */
		/*
		TODO: Finish the rest of this.

		$floors = $this->em->createQuery('SELECT f FROM App:Floor f WHERE f.dungeon = :dungeon ORDER BY f.actualDepth DESC')->setParameters(['dungeon' => $dungeon])->getResult();

		# Spawner loop, commence!
		foreach ($floors as $floor) {
			$this->checkFloor($floor);
		}
		*/

	}

	public function checkFloor(Floor $floor): void {
		$rooms = $floor->getRooms();
		$roomCount = $rooms->count();
		$popRate = $floor->getPopRate();
		if (!$popRate) {
			$popRate = $roomCount * $this->basePopRate;
			$floor->setPopRate($popRate);
		}
		$monsters = $floor->getMonsters()->count();
		$spawners = $floor->fetchSpawnableRooms();
		if ($monsters === 0 || $spawners === 0) {
			$currentRate = 0;
		} else {
			$currentRate = $monsters / $popRate;
		}
		while ($currentRate < $popRate) {
			$monster = $this->spawnMob($floor);
			$where = $this->findMonsterSpawnRoom($monster, $spawners);
			if ($where) {
				$monster->setRoom($where);
				$monster->setFloor($floor);
			}
		}
	}

	/**
	 * Returns a room from a collection of rooms that a particular monster can spawn in.
	 *
	 * @param Monster $monster
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
		$max = $random * 2;
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
	 *
	 * @param Monster $monster
	 * @param Room    $room
	 *
	 * @return bool
	 */
	public function validateMonsterSpawn(Monster $monster, Room $room): bool {
		#TODO: Actually add some validation rooms, so we can keep monsters out of certain rooms, or stop over population, or whatever we want.
		return true;
	}

	public function characterAttackMob(Character $char, Monster $target): void {
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

	public function mobAttackCharacter(Monster $mob, Character $target): void {
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

	public function spawnMob(Floor $floor): Monster {
		$monster = new Monster;
		$this->em->persist($monster);
		$monster->setRace($this->pickMonsterType($floor));
		$monster->setPlayerKills(0);
		$monster->setHealth($this->calculateMaxHealth($monster));
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

	public function pickMonsterType($floor): MonsterType {

	}

	public function calculateEnergyCost(Character $char, string $action) {
		switch ($action) {
			case 'move':
				$skill = $this->fetchSkill($char, 'dungeoneering');
				# 10000 is the highest skill we'll worry about, so subtract the evaluated skill from that, then divide by 10000.
				# This reduces it to a 5th place decimal: 0.#####.
				$cost = (100000 - $skill->evaluate()) / 100000;
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
		return $this->em->getRepository(SkillType::class)->findOneBy(['name' => $name]) ?: false;
	}

	public function moveCharacter(Character $char, Transit $where): void {
		$oldRoom = $char->getRoom();
		$newRoom = $where->getToRoom();
		$char->setRoom($newRoom);
		$char->setLastRoom($oldRoom);
		$newRoom->setVisits($newRoom->getVisits() + 1);
		$where->setTransits($where->getTransits() + 1);
		$char->setEnergy($char->getEnergy() - $this->calculateEnergyCost($char, 'move'));
		$this->trainSkill($char, $this->findSkillTypeByName('dungeoneering'), 1);
		$this->em->flush();
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

	public function trainSkill(Character $char, SkillType $type = null, $pract = 0, $theory = 0): bool {
		if (!$type) {
			# Not all weapons have skills, this just catches those.
			return false;
		}
		$query = $this->em->createQuery('SELECT s FROM App:Skill s WHERE s.character = :me AND s.type = :type ORDER BY s.id ASC')->setParameters([
			'me' => $char,
			'type' => $type,
		])->setMaxResults(1);
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
			echo 'updating skill ' . $training->getId() . ' - ';
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
