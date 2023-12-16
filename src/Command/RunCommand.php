<?php

namespace App\Command;

use App\Entity\AppSetting;
use App\Entity\Dungeon;
use App\Service\DungeonMaster;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command {
	private EntityManagerInterface $em;
	private DungeonMaster $dm;

	public function __construct(DungeonMaster $dm, EntityManagerInterface $em) {
		$this->dm = $dm;
		$this->em = $em;

		parent::__construct();
	}

	protected function configure() {
		$this->setName('hota:run')->setDescription('Main game loop. Designed to self-throttle.');
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
		$output->writeln("Hauls of the Abyss -- Running!");
		$dungeons = $this->em->getRepository(Dungeon::class)->findAll();
		$dungeonCount = 0;
		foreach ($dungeons as $each) {
			$name = $each->getName();
			$output->writeln("Checking $name...");
			$dungeonCount++;
			$this->dm->checkDungeon($each);
		}

		# This is basically a game initialization check.
		if ($dungeonCount === 0) {
			$output->writeln("Creating The Abyss...");
			$dungeon = new Dungeon();
			$this->em->persist($dungeon);
			$dungeon->setName("The Abyss");
			$dungeon->setType("abyssal");
			$this->em->flush();
			$this->dm->checkDungeon($dungeon);
		}

		$end = microtime(true);
		$time = $end - $start;
		$output->writeln("Hauls of the Abyss -- Initialized! -- $time seconds.");
		return Command::SUCCESS;
	}
}
