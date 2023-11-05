<?php

namespace App\DataFixtures;

use App\Entity\Origin;
use App\Entity\OriginSkill;
use App\Entity\SkillType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OriginFixtures extends Fixture implements DependentFixtureInterface {

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
		echo "Loading Origins...\n";
		foreach ($this->origins as $name => $data) {
			$origin = $manager->getRepository(Origin::class)->findOneBy(['name'=>$name]);
			echo "Loading $name data...\n";
			if (!$origin) {
				echo "Origin not found. Creating...\n";
				$origin = new Origin;
				$manager->persist($origin);
				$origin->setName($name);
			}
			if (array_key_exists('unlock', $data)) {
				$origin->setUnlock($data['unlock']);
				$origin->setPublic(false);
			} else {
				$origin->setPublic(true);
			}
			$manager->flush();
			echo "Updating existing skills...\n";
			foreach ($origin->getSkills() as $skill) {
				$found = false;
				$name = $skill->getSkill()->getName();
				echo "Looking for updated data for '$name'...\n";
				foreach ($data['skills'] as $sName => $sData) {
					if ($name === $sName) {
						echo "Data found. Updating...\n";
						$found = true;
						$skill->setMod($sData);
						unset($data['skills'][$sName]);
					}
				}
				if (!$found) {
					echo "Data not found. Removing skill...\n";
					$manager->remove($skill);
				}
			}
			$manager->flush();
			echo "Associating new skills...\n";
			foreach ($data['skills'] as $sName => $sData) {
				echo "Adding data for $sName...\n";
				$skillType = $manager->getRepository(SkillType::class)->findOneBy(['name'=>$sName]);
				if (!$skillType) {
					echo "SkillType for '$sName' not found... skipping... \n";
				} else {
					echo "Matching SkillType located. Adding Origin association...\n";
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
