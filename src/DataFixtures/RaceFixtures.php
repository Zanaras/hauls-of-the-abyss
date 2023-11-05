<?php

namespace App\DataFixtures;

use App\Entity\Race;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RaceFixtures extends Fixture {

	# $abilities is the list of get/set functions for race ability scores.
	private array $abilities = ['Agility', 'Charisma', 'Constitution', 'Endurance', 'Intelligence', 'Spirit', 'Perception'];

	# $races is the list of races in a two level array. First level is the race name as key to it's attributes. Second level is the size and abilities.
	private array $races = [
		'human' => [
			'size' => 'medium',
			'public' => true,
		],
		'goblin' => [
			'size' => 'small',
			'Agility' => 1.2,
			'Charisma' => 0.7,
			'Constitution' => 0.7,
			'Endurance' => 1.2,
			'Intelligence' => 1,
			'Spirit' => 1,
			'Perception' => 1.2,
			'public' => true,
		],
	];

	public function load(ObjectManager $manager): void {
		echo "Loading Races...\n";
		foreach ($this->races as $name=>$data) {
			echo "... Hello $name race!\n";
			$race = $manager->getRepository(Race::class)->findOneBy(['name'=>$name]);
			if (!$race) {
				echo "New race detected. Adding!\n";
				$race = new Race();
				$manager->persist($race);
				$race->setName($name);
			}
			$race->setSize($data['size']);
			foreach ($this->abilities as $score) {
				if (array_key_exists($score, $data)) {
					$race->{'set'.$score}($data[$score]);
				} else {
					$race->{'set'.$score}(1); #Default values for these are 1.
				}
			}
		}

		$manager->flush();
	}
}
