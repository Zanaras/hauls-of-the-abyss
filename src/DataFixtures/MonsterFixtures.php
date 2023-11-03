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
            'attackType' => [
                'physical',
                'magic'
            ],
		],
        'skeleton' => [
            'size' => 'medium',
            'image' => '../images/skeleton.png',
            'attackType' => [
                'physical',
            ],
        ],
		'goblin' => [
			'size' => 'small',
            'image' => '../images/goblin.png',
            'attackType' => [
                'physical',
            ],
		],
		'imp' => [
			'size' => 'small',
            'image' => '../images/imp.png',
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
			$monster->setAttackTypes($data['attackType']);
		}

		$manager->flush();
	}
}
