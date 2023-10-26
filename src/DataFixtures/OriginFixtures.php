<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OriginFixtures extends Fixture {

	private array $origins = [
		'peaceful' => [
			'public' => true,
			'skills' => []
		]
	];

	public function load(ObjectManager $manager): void {
		// $product = new Product();
		// $manager->persist($product);

		$manager->flush();
	}
}
