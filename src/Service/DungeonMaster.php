<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Journal;
use App\Entity\Skill;
use App\Entity\SkillType;
use App\Entity\Transit;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class DungeonMaster {

	protected EntityManagerInterface $em;
	protected TranslatorInterface $trans;

	public function __construct(EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->em = $em;
		$this->trans = $trans;
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
