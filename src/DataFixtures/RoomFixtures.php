<?php

namespace App\DataFixtures;

use App\Entity\Race;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoomFixtures extends Fixture {

	private array $types = [
		'normal' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
		],
		'deep pit' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>true,
			'modifiers'=>false,
		],
		'pit bottom' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
			'alternate'=>'normal',
		],
		'teleporter' => [
			'spawn'=>false,
			'teleporter'=>true,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
		],
		'portal' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>true,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
		],
		'stairs' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>true,
			'allowDown'=>true,
			'modifiers'=>false,
		],
	];

	public function load(ObjectManager $manager): void {
		echo 'Loading RoomTypes...';
		foreach ($this->types as $name=>$data) {
			$type = $manager->getRepository('App:RoomType')->findOneBy(['name'=>$name]);
			if (!$type) {
				echo 'New race detected. Adding!';
				$type = new Race();
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setSpawn($data['spawn']?:false);
			$type->setTeleporter($data['teleporter']?:false);
			$type->setPortal($data['portal']?:false);
			$type->setAllowUp($data['allowUp']?:false);
			$type->setAllowDown($data['allowDown']?:false);
			$type->setModifiers($data['modifiers']?:[]);
		}

		$manager->flush();
	}
}
