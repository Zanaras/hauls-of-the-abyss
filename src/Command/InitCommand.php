<?php

namespace App\Command;

use App\Entity\AppSetting;
use App\Entity\Race;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command {
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;

		parent::__construct();
	}

	protected function configure() {
		$this->setName('hota:init')->setDescription('Initializes the game environment with a basic set of object templates.');
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$start = microtime(true);
		$em = $this->em;
		$output->writeln("Hauls of the Abyss -- Initalizing!");
		$output->writeln(" -- Checking for previous initialization...");
		$query = $em->createQuery('SELECT s FROM App:AppSetting s WHERE s.name = :name')->setParameters(['name' => 'init'])->setMaxResults(1);
		$result = $query->getResult();
		if (!$result) {
			$output->writeln("   -- None detected!");
			$init = new AppSetting;
			$init->setName('init');
			$init->setValue('started');
		} elseif ($result->getValue() !== 'completed') {
			$output->writeln("   -- Initialization completion value is not completed! Starting new initialization process!");
		} else {
			$output->writeln("   -- Completed intilization detected! Aborting!");
			return Command::INVALID;
		}
		$newRaces = 0;
		$updatedRaces = 0;
		$output->writeln("-- Checking for Races...");
		$initRaces = $_ENV['INIT_RACES'];
		if ($initRaces) {
			$initRaces = json_decode($initRaces);
			foreach ($initRaces as $each) {
				$output->writeln("---- Searching for " . $each['name']);
				$race = $em->createQuery('SELECT r FROM App:Race r WHERE r.name = :name')->setParameters(['name' => $each['name']])->getResult();
				if ($race) {
					$output->writeln("------ " . $each['name'] . " Race deteced. Skipping Race creation");
					if ($race->getAgility() !== $each['agility']) $race->setAgility($each['agility']);
					if ($race->getCharisma() !== $each['charisma']) $race->setCharisma($each['agility']);
					if ($race->getConstitution() !== $each['constitution']) $race->setConstitution($each['constitution']);
					if ($race->getEndurance() !== $each['endurance']) $race->setEndurance($each['endurance']);
					if ($race->getIntelligence() !== $each['intelligence']) $race->setIntelligence($each['intelligence']);
					if ($race->getSpirit() !== $each['spirit']) $race->setSpirit($each['spirit']);
					if ($race->getPerception() !== $each['perception']) $race->setPerception($each['perception']);
					$updatedRaces++;
				} else {
					$output->writeln("------ " . $each['name'] . " Race NOT detected. Adding...");
					$race = new Race();
					$race->setName($each['name']);
					$em->persist($race);
					$race->setAgility($each['agility']);
					$race->setCharisma($each['charisma']);
					$race->setConstitution($each['constitution']);
					$race->setEndurance($each['endurance']);
					$race->setIntelligence($each['intelligence']);
					$race->setSpirit($each['spirit']);
					$race->setPerception($each['perception']);
					$output->writeln("------ " . $each['name'] . " Race added.");
					$newRaces++;
				}
				$em->flush();
			}
		}

		$end = microtime(true);
		$time = $end - $start;
		$output->writeln("Hauls of the Abyss -- Initialized! -- $time seconds.");
		return Command::SUCCESS;
	}
}
