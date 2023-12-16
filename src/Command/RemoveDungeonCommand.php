<?php

namespace App\Command;

use App\Entity\Dungeon;
use App\Entity\Transit;
use App\Service\DungeonMaster;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveDungeonCommand extends Command {
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure() {
		$this->setName('hota:dungeon:remove')->setDescription('Removes a dungeon safely..');
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
		$output->writeln("Hauls of the Abyss -- Safely deleting the dungeon!");
		$dungeon = $this->em->createQuery('SELECT d FROM App:Dungeon d')->getSingleResult();
		$parameters = ['dungeon'=>$dungeon->getId()];
		$this->em->createQuery('UPDATE App:Character c SET c.room = null WHERE c.dungeon = :dungeon')->setParameters($parameters)->execute();
		$this->em->createQuery('UPDATE App:Character c SET c.lastRoom = null WHERE c.dungeon = :dungeon')->setParameters($parameters)->execute();
		$this->em->createQuery('UPDATE App:Character c SET c.dungeon = null WHERE c.dungeon = :dungeon')->setParameters($parameters)->execute();
		$this->em->createQuery('DELETE FROM App:Transit t WHERE t.dungeon = :dungeon')->setParameters($parameters)->execute();
		$this->em->createQuery('DELETE FROM App:Room r WHERE r.dungeon = :dungeon')->setParameters($parameters)->execute();
		$this->em->createQuery('DELETE FROM App:Floor f WHERE f.dungeon = :dungeon')->setParameters($parameters)->execute();
		$this->em->createQuery('DELETE FROM App:Dungeon d WHERE d.id = :dungeon')->setParameters($parameters)->execute();
		$end = microtime(true);
		$time = $end - $start;
		$output->writeln("Hauls of the Abyss -- Dungeon deleted! -- $time seconds.");
		return Command::SUCCESS;
	}
}
