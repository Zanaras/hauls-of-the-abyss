<?php

namespace App\DataFixtures;

use App\Entity\Origin;
use App\Entity\OriginSkill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OriginFixtures extends Fixture {

	private array $origins = [
		'earthDeath' => [
			'public' => true,
			'skills' => [
				'first aid' => 1.1,
				'human anatomy' => 1.1,
				
			]
		],
		'fantasyDeath' => [
			'public' => true,
			'skills' => [
				'dungeoneering' => 1.1,
			]
		],
		'fantasyPortal' => [
			'public' => true,
			'skills' => [
				'dungeoneering' => 1.1,
			]
		],
		'mafDeath' => [
			'public' => false,
			'unlock' => 'maf',
			'skills' => [
				'short sword' => 1.1,
				'long sword' => 1.1,
				'first aid' => 0.9,
				'surgery' => 0.9,
				'farming' => 0.9,
				'husbandry' => 0.9,
				'hunting' => 0.9,
				'human anatomy' => 1.1,
				'riding' => 1.1,
			],
		],
		'mafPortal' => [
			'public' => false,
			'unlock' => 'maf',
			'skills' => [
				'short sword' => 1.1,
				'long sword' => 1.1,
				'first aid' => 0.9,
				'surgery' => 0.9,
				'farming' => 0.9,
				'husbandry' => 0.9,
				'hunting' => 0.9,
				'human anatomy' => 1.1,
				'riding' => 1.1,
			],
		],
		'bmDeath' => [
			'public' => false,
			'unlock' => 'bm',
			'skills' => [
				'short sword' => 1.1,
				'long sword' => 1.1,
				'flatbow' => 0.9,
				'longbow' => 0.9,
				'crossbow' => 0.9,
				'human anatomy' => 1.1,
				'riding' => 1.1,
			],
		],
		'bmPortal' => [
			'public' => false,
			'unlock' => 'bm',
			'skills' => [
				'short sword' => 1.2,
				'long sword' => 1.2,
				'flatbow' => 0.8,
				'longbow' => 0.8,
				'crossbow' => 0.7,
				'human anatomy' => 1.2,
				'riding' => 1.3,
			],
		],
		'dungeonMaster' => [
			'public' => false,
			'unlock' => 'dm',
			'skills' => [
				'dungeoneering' => 1.1,
				'invocation' => 1.1,
				'inscription' => 1.1,
				'insectoid anatomy' => 1.1,
			]
		],
		'worldBuilder' => [
			'public' => false,
			'unlock' => 'lemuria',
			'skills' => [
				'invocation' => 1.1,
				'inscription' => 1.1,
				'insectoid anatomy' => 1.1,
			]
		]
	];

	public function load(ObjectManager $manager): void {
		echo 'Loading Origins...';
		foreach ($this->origins as $name => $data) {
			$origin = $manager->getRepository('App:Origin')->findOneBy(['name'=>$name]);
			echo 'Loading '.$name.' data...';
			if (!$origin) {
				echo 'Origin not found. Creating...';
				$origin = new Origin;
				$manager->persist($origin);
				$origin->setName($name);
			}
			$origin->setUnlock($data['unlock']);
			$manager->flush();
			echo 'Updating existing skills...';
			foreach ($origin->getSkills() as $skill) {
				$found = false;
				$name = $skill->getSkill()->getName();
				echo 'Looking for updated data for '.$name.'...';
				foreach ($data['skills'] as $sName => $sData) {
					if ($name === $sName) {
						echo 'Data found. Updating...';
						$found = true;
						$skill->setMod($sData);
						unset($data['skills'][$sName]);
					}
				}
				if (!$found) {
					echo 'Data not found. Removing skill...';
					$manager->remove($skill);
				}
			}
			$manager->flush();
			echo 'Associating new skills...';
			foreach ($data['skills'] as $sName => $sData) {
				echo 'Adding data for '.$sName.'...';
				$skillType = $manager->getRepository('App:SkillType')->findOneBy(['name'=>$sName]);
				if (!$skillType) {
					echo 'SkillType for '.$sName.' not found... skipping...';
				} else {
					echo 'Matching SkillType located. Adding Origin association...';
					$oSkill = new OriginSkill();
					$manager->persist($oSkill);
					$oSkill->setSkill($skillType);
					$oSkill->setOrigin($origin);
				}
			}
			$manager->flush();
		}
	}
	public function getDependencies(): array {
		return [
			SkillFixtures::class,
		];
	}
}
