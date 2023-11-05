<?php

namespace App\DataFixtures;

use App\Entity\SkillCategory;
use App\Entity\SkillType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SkillFixtures extends Fixture {
	private array $categories = [
		# Tier 0
		"equipment" => ['pro' => null],
		"leadership" => ['pro' => null],
		"survival" => ['pro' => null],
		"gathering" => ['pro' => null],
		"construction" => ['pro' => null],
		"combat" => ['pro' => null],
		"magic" => ['pro' => null],
		"medical" => ['pro' => null],

		# Tier 1
		"bows" => ['pro' => "equipment"],
		"crossbows" => ['pro' => "equipment"],
		"thrown" => ['pro' => "equipment"],
		"slings" => ['pro' => "equipment"],
		"axes" => ['pro' => "equipment"],
		"swords" => ['pro' => "equipment"],
		"polearms" => ['pro' => "equipment"],
		"gloves" => ['pro' => "equipment"],
		"daggers" => ['pro' => "equipment"],
		"clubs" => ['pro' => "equipment"],
		"sickles" => ['pro' => "equipment"],
		"flails" => ['pro' => "equipment"],
		"hammers" => ['pro' => "equipment"],

		"command" => ['pro' => "leadership"],
		"governance" => ['pro' => "leadership"],

		"medicine" => ['pro' => "medical"],
		"anatomy" => ['pro' => "medical"],

		"humanoids" => ['pro' => "anatomy"],
		"monsters" => ['pro' => "anatomy"],
	];
	private array $skills = [
		"tracking" => ['cat' => 'survival'],
		"riding" => ['cat' => 'survival'],
		"dungeoneering" => ['cat' => 'survival'],
		"outdoorsmanship" => ['cat' => 'survival'],

		"first aid" => ['cat' => 'medical'],
		"surgery" => ['cat' => 'medical'],

		"human anatomy" => ['cat' => 'humanoids'],
		"goblin anatomy" => ['cat' => 'humanoids'],
		"insectoid anatomy" => ['cat' => 'humanoids'],

		"invocation" => ['cat' => 'magic'],
		"inscription" => ['cat' => 'magic'],
		"life magic" => ['cat' => 'magic'],
		"death magic" => ['cat' => 'magic'],
		"fire magic" => ['cat' => 'magic'],
		"water magic" => ['cat' => 'magic'],
		"air magic" => ['cat' => 'magic'],
		"earth magic" => ['cat' => 'magic'],
		"light magic" => ['cat' => 'magic'],
		"dark magic" => ['cat' => 'magic'],
		"portals" => ['cat' => 'magic'],
		"teleportation" => ['cat' => 'magic'],

		"hunting" => ['cat' => "gathering"],
		"husbandry" => ['cat' => "gathering"],
		"farming" => ['cat' => "gathering"],

		"short sword" => ['cat' => 'swords'],
		"long sword" => ['cat' => 'swords'],
		"saber" => ['cat' => 'swords'],
		"katana" => ['cat' => 'swords'],
		"rapier" => ['cat' => 'swords'],
		"machete" => ['cat' => 'swords'],

		"knife" => ['cat' => 'daggers'],
		"dagger" => ['cat' => 'daggers'],

		"battle axe" => ['cat' => 'axes'],
		"great axe" => ['cat' => 'axes'],

		"club" => ['cat' => 'clubs'],
		"mace" => ['cat' => 'clubs'],
		"morning star" => ['cat' => 'clubs'],

		"pike" => ['cat' => 'polearms'],
		"spear" => ['cat' => 'polearms'],
		"halberd" => ['cat' => 'polearms'],
		"glaive" => ['cat' => 'polearms'],
		"staff" => ['cat' => 'polearms'],
		"lance" => ['cat' => 'polearms'],
		"swordstaff" => ['cat' => 'polearms'],

		"unarmed" => ['cat' => 'gloves'],
		"claws" => ['cat' => 'gloves'],

		"flail" => ['cat' => 'flails'],
		"chain mace" => ['cat' => 'flails'],
		"nunchaku" => ['cat' => 'flails'],
		"triple staff" => ['cat' => 'flails'],

		"sickle" => ['cat' => 'sickles'],
		"kusarigama" => ['cat' => 'sickles'],
		"war scythe" => ['cat' => 'sickles'],
		"fauchard" => ['cat' => 'sickles'],

		"war hammer" => ['cat' => 'hammers'],
		"maul" => ['cat' => 'hammers'],
		"totokia" => ['cat' => 'hammers'],
		"war mallet" => ['cat' => 'hammers'],

		"sling" => ['cat' => 'slings'],
		"staff sling" => ['cat' => 'slings'],

		"flatbow" => ['cat' => 'bows'],
		"longbow" => ['cat' => 'bows'],
		"crossbow" => ['cat' => 'bows'],

		"throwing knife" => ['cat' => 'thrown'],
		"throwing axe" => ['cat' => 'thrown'],
		"javelin" => ['cat' => 'thrown'],
		"boomerang" => ['cat' => 'thrown'],
		"chakram" => ['cat' => 'thrown'],
	];

	public function load(ObjectManager $manager): void {
		echo "Loading Skill Categories...\n";
		foreach ($this->categories as $name => $data) {
			$type = $manager->getRepository(SkillCategory::class)->findOneBy(['name' => $name]);
			if (!$type) {
				$type = new SkillCategory();
				$manager->persist($type);
				$type->setName($name);
			}
			if ($data['pro'] != null) {
				$pro = $manager->getRepository(SkillCategory::class)->findOneBy(['name' => $data['pro']]);
				if ($pro) {
					$type->setCategory($pro);
				} else {
					echo "No Skill Category of name " . $data['pro'] . " found for $name\n";
				}
			}
			$manager->flush();
		}
		echo "Loading Skill Types...";
		foreach ($this->skills as $name => $data) {
			$type = $manager->getRepository(SkillType::class)->findOneBy(['name' => $name]);
			if (!$type) {
				$type = new SkillType();
				$manager->persist($type);
				$type->setName($name);
			}
			$cat = $manager->getRepository(SkillCategory::class)->findOneBy(['name' => $data['cat']]);
			if ($cat) {
				$type->setCategory($cat);
			} else {
				echo "No Skill category of name " . $data['cat'] . " found for skill $name\n";
			}
		}
		$manager->flush();
	}
}
