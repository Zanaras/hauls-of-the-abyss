<?php

namespace App\DataFixtures;

use App\Entity\Race;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TransitFixtures extends Fixture {

	private array $types = [
		'normal' => [],
	];

	public function load(ObjectManager $manager): void {
		echo 'Loading TransitTypes...';
		foreach ($this->types as $name=>$data) {
			echo '... Hello '.$name.' race!';
			$type = $manager->getRepository('App:TransitType')->findOneBy(['name'=>$name]);
			if (!$type) {
				echo 'New race detected. Adding!';
				$type = new TransitType();
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setModifiers($data);
		}
		$manager->flush();
	}
}
