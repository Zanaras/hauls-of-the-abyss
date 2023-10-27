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

		$end = microtime(true);
		$time = $end - $start;
		$output->writeln("Hauls of the Abyss -- Initialized! -- $time seconds.");
		return Command::SUCCESS;
	}
}
