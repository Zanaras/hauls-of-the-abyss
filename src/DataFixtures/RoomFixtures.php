<?php

namespace App\DataFixtures;

use App\Entity\RoomType;
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
			'minDepth'=>1,
		],
		'deep pit' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>true,
			'modifiers'=>false,
			'minDepth'=>1,
		],
		'pit bottom' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
			'alternate'=>'normal',
			'minDepth'=>3,
		],
		'teleporter' => [
			'spawn'=>false,
			'teleporter'=>true,
			'portal'=>false,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
			'minDepth'=>5,
		],
		'portal' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>true,
			'allowUp'=>false,
			'allowDown'=>false,
			'modifiers'=>false,
			'minDepth'=>25,
		],
		'stairs' => [
			'spawn'=>false,
			'teleporter'=>false,
			'portal'=>false,
			'allowUp'=>true,
			'allowDown'=>true,
			'modifiers'=>false,
			'minDepth'=>1,
		],
	];

	# Default values for missing array keys above.
	const SPAWN = false;
	const TELEPORTER = false;
	const PORTAL = false;
	const ALLOWUP = false;
	const ALLOWDOWN = false;
	const MODIFIERS = [];
	const MINDEPTH = 1;

	/**
	 * Loads the above array(s) into the RoomType Database table.
	 * Fairly resilient, assumes default values for missing entries.
	 * @param ObjectManager $manager
	 *
	 * @return void
	 */
	public function load(ObjectManager $manager): void {
		echo "Loading RoomTypes...\n";
		foreach ($this->types as $name=>$data) {
			$type = $manager->getRepository(RoomType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				echo "New $name detected. Adding!";
				$type = new RoomType();
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setSpawn($data['spawn']?:self::SPAWN);
			$type->setTeleporter($data['teleporter']?:self::TELEPORTER);
			$type->setPortal($data['portal']?:self::PORTAL);
			$type->setAllowUp($data['allowUp']?:self::ALLOWUP);
			$type->setAllowDown($data['allowDown']?:self::ALLOWDOWN);
			$type->setModifiers($data['modifiers']?:self::MODIFIERS);
			$type->setMinDepth($data['minDepth']?:self::MINDEPTH);
		}

		$manager->flush();
	}
}
