<?php

namespace App\DataFixtures;

use App\Entity\TransitType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TransitFixtures extends Fixture {

	private array $types = [
		'normal' => [],
		'stairs' => [],
		'deep pit' => ['fall damage'],
	];

	public function load(ObjectManager $manager): void {
		echo "Loading TransitTypes...\n";
		foreach ($this->types as $name=>$data) {
			echo "... Hello $name race!";
			$type = $manager->getRepository(TransitType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				echo "New race detected. Adding!\n";
				$type = new TransitType();
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setModifiers($data);
		}
		$manager->flush();
	}
}
