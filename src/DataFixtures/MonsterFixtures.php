<?php

namespace App\DataFixtures;

use App\Entity\Monster;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MonsterFixtures extends Fixture {

	private array $types = [
		'slime' => [
			'size' => 'medium',
            'image' => '../images/slime.png',
            'imageDead' => '../images/slimeDead.png',
            'neutral' => true,
            'attackType' => [
                'physical',
                'magic'
            ],
		],
		'mimic' => [
			'size' => 'medium',
            'image' => '../images/mimic.png',
            'imageDead' => '../images/mimicDead.png',
            'neutral' => true,
            'attackType' => [
                'physical',
            ],
		],
        'skeleton' => [
            'size' => 'medium',
            'image' => '../images/skeleton.png',
            'imageDead' => '../images/skeletonDead.png',
            'neutral' => false,
            'attackType' => [
                'physical',
            ],
        ],
		'goblin' => [
			'size' => 'small',
            'image' => '../images/goblin.png',
            'imageDead' => '../images/goblinDead.png',
            'neutral' => false,
            'attackType' => [
                'physical',
            ],
		],
		'imp' => [
			'size' => 'small',
            'image' => '../images/imp.png',
            'imageDead' => '../images/impDead.png',
            'neutral' => false,
            'attackType' => [
                'magic',
            ],
		],
	];

	public function load(ObjectManager $manager): void {
		echo 'Loading MonsterTypes...';
		foreach ($this->monsters as $name=>$data) {
			$monster = $manager->getRepository('App:MonsterType')->findOneBy(['name'=>$name]);
			if (!$name) {
				echo 'New Monster detected. Adding!';
				$name = new Monster();
				$manager->persist($name);
				$name->setName($name);
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
