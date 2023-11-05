<?php

namespace App\DataFixtures;

use App\Entity\MonsterType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MonsterFixtures extends Fixture {
	private array $abilities = ['Agility', 'Charisma', 'Constitution', 'Endurance', 'Intelligence', 'Spirit', 'Perception'];
	private array $types = [
		'green slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-green.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.5,
			'Charisma' => 1,
			'Constitution' => 1,
			'Endurance' => 1,
			'Intelligence' => 1,
			'Spirit' => 1,
			'Perception' => 1,
		],
		'blue slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-blue.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1,
			'Charisma' => 1,
			'Constitution' => 1,
			'Endurance' => 1,
			'Intelligence' => 1,
			'Spirit' => 1.5,
			'Perception' => 1,
		],
		'indigo slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-indigo.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1,
			'Charisma' => 1,
			'Constitution' => 1,
			'Endurance' => 1,
			'Intelligence' => 1,
			'Spirit' => 1,
			'Perception' => 1.5,
		],
		'orange slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-orange.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1,
			'Charisma' => 1,
			'Constitution' => 1,
			'Endurance' => 1.5,
			'Intelligence' => 1,
			'Spirit' => 1,
			'Perception' => 1,
		],
		'purple slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-purple.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1,
			'Charisma' => 1,
			'Constitution' => 1,
			'Endurance' => 1,
			'Intelligence' => 1.5,
			'Spirit' => 1,
			'Perception' => 1,
		],
		'red slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-red.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1,
			'Charisma' => 1,
			'Constitution' => 1.5,
			'Endurance' => 1,
			'Intelligence' => 1,
			'Spirit' => 1,
			'Perception' => 1,
		],
		'yellow slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/common-slime-yellow.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1,
			'Charisma' => 1.5,
			'Constitution' => 1,
			'Endurance' => 1,
			'Intelligence' => 1,
			'Spirit' => 1,
			'Perception' => 1,
		],
		'green deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-green.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.7,
			'Charisma' => 1.2,
			'Constitution' => 1.2,
			'Endurance' => 1.2,
			'Intelligence' => 1.2,
			'Spirit' => 1.2,
			'Perception' => 1.2,
		],
		'blue deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-blue.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.2,
			'Charisma' => 1.2,
			'Constitution' => 1.2,
			'Endurance' => 1.2,
			'Intelligence' => 1.2,
			'Spirit' => 1.7,
			'Perception' => 1.2,
		],
		'indigo deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-indigo.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.2,
			'Charisma' => 1.2,
			'Constitution' => 1.2,
			'Endurance' => 1.2,
			'Intelligence' => 1.2,
			'Spirit' => 1.2,
			'Perception' => 1.7,
		],
		'orange deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-orange.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.2,
			'Charisma' => 1.2,
			'Constitution' => 1.2,
			'Endurance' => 1.7,
			'Intelligence' => 1.2,
			'Spirit' => 1.2,
			'Perception' => 1.2,
		],
		'purple deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-purple.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.2,
			'Charisma' => 1.2,
			'Constitution' => 1.2,
			'Endurance' => 1.2,
			'Intelligence' => 1.7,
			'Spirit' => 1.2,
			'Perception' => 1.2,
		],
		'red deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-red.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.2,
			'Charisma' => 1.2,
			'Constitution' => 1.7,
			'Endurance' => 1.2,
			'Intelligence' => 1.2,
			'Spirit' => 1.2,
			'Perception' => 1.2,
		],
		'yellow deadly slime' => [
			'size' => 'medium',
			'image' => 'heavenscorn/slimes/deadly-slime-yellow.png',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.2,
			'Charisma' => 1.7,
			'Constitution' => 1.2,
			'Endurance' => 1.2,
			'Intelligence' => 1.2,
			'Spirit' => 1.2,
			'Perception' => 1.2,
		],
		'mimic' => [
			'size' => 'medium',
			'image' => '',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
			'Agility' => 1.5,
			'Charisma' => 1,
			'Constitution' => 2.5,
			'Endurance' => 1,
			'Intelligence' => 1,
			'Spirit' => 0,
			'Perception' => 0.2,
		],
		'skeleton' => [
			'size' => 'medium',
			'image' => '',
			'imageDead' => '',
			'neutral' => false,
			'attackType' => [
				'physical',
			],
		],
		'goblin' => [
			'size' => 'small',
			'image' => '',
			'imageDead' => '',
			'neutral' => false,
			'attackType' => [
				'physical',
			],
		],
		'imp' => [
			'size' => 'small',
			'image' => '',
			'imageDead' => '',
			'neutral' => false,
			'attackType' => [
				'magic',
			],
		],
	];

	public function load(ObjectManager $manager): void {
		echo "Loading MonsterTypes...\n";
		foreach ($this->types as $name => $data) {
			$monster = $manager->getRepository(MonsterType::class)->findOneBy(['name' => $name]);
			if (!$monster) {
				echo "New Monster detected. Adding!\n";
				$monster = new MonsterType();
				$manager->persist($monster);
				$monster->setName($name);
			}
			$monster->setSize($data['size']);
			$monster->setImage($data['image']);
			$monster->setImageDead($data['imageDead']);
			$monster->setNeutral($data['neutral']);
			$monster->setAttackTypes($data['attackType']);

			foreach ($this->abilities as $score) {
				if (array_key_exists($score, $data)) {
					$monster->{'set'.$score}($data[$score]);
				} else {
					$monster->{'set'.$score}(1); #Default values for these are 1.
				}
			}
		}

		$manager->flush();
	}
}
