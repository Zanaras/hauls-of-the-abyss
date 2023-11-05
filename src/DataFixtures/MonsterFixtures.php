<?php

namespace App\DataFixtures;

use App\Entity\MonsterType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MonsterFixtures extends Fixture {
	private array $types = [
		'slime' => [
			'size' => 'medium',
			'image' => '',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
				'magic',
			],
		],
		'mimic' => [
			'size' => 'medium',
			'image' => '',
			'imageDead' => '',
			'neutral' => true,
			'attackType' => [
				'physical',
			],
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
		}

		$manager->flush();
	}
}
