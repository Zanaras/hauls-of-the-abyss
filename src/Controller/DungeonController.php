<?php

namespace App\Controller;

use App\Entity\GuideKeeper;
use App\Entity\Room;
use App\Entity\Transit;
use App\Service\DungeonMaster;
use App\Service\GateKeeper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DungeonController extends AbstractController {

	private DungeonMaster $dm;
	private EntityManagerInterface $em;
	private GateKeeper $gk;
	private TranslatorInterface $trans;

	public function __construct(DungeonMaster $dm, EntityManagerInterface $em, GateKeeper $gk, TranslatorInterface $trans) {
		$this->dm = $dm;
		$this->em = $em;
		$this->gk = $gk;
		$this->trans = $trans;
	}

	#[Route('/dungeon', name: 'dungeon_status')]
	public function index(): Response {
		$char = $this->gk->gateway('dungeon_status');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		return $this->render('dungeon/index.html.twig', [
			'options' => $this->gk->dungeonNav($char),
		]);
	}

	#[Route('/dungeon/toTown',name: 'dungeon_to_town')]
	public function dungeonToTown(): RedirectResponse {
		$char = $this->gk->gateway('dungeon_to_town');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		$char->setAreaCode($this->gk::DUNGEON);
		$this->em->flush();
		return $this->redirectToRoute('town_status');
	}

	#[Route('/dungeon/enter',name: 'dungeon_enter')]
	public function dungeonEnter(): RedirectResponse {
		$char = $this->gk->gateway('dungeon_enter');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		$start = $this->em->getRepository(Room::class)->findOneBy(['dungeonExit'=>true]);
		$char->setRoom($start);
		$char->setDungeon($start->getDungeon());
		$start->setVisits($start->getVisits()+1);
		$floor = $start->getFloor();
		$floor->setVisits($floor->getVisits()+1);
		$this->em->flush();
		return $this->redirectToRoute('dungeon_status');
	}

	#[Route('/dungeon/exit',name: 'dungeon_exit')]
	public function dungeonExit(): RedirectResponse {
		$char = $this->gk->gateway('dungeon_exit');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		$char->setRoom(null);
		$char->setDungeon(null);
		$this->em->flush();
		return $this->redirectToRoute('dungeon_status');
	}

	#[Route('/move/{dir}', name: 'dungeon_move', requirements:['dir'=>'(NW|N|NE|E|SE|S|SW|W|D|U){1,2}'])]
	public function move(string $dir): Response {
		[$char, $transit] = $this->gk->gateway('dungeon_move', ['dir'=>$dir], ['dir'=>$dir]);
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}

		# If we don't have an error, this is a legitimate move.
		$oldRoom = $char->getRoom();
		$this->dm->moveCharacter($char, $transit);
		$fromDir = $transit->getToRoom()->findFromDirection($oldRoom, true);
		$this->addFlash('notice', "You enter the room from the $fromDir.");

		return $this->redirectToRoute('dungeon_status');
	}

	#[Route('/retreat', name: 'dungeon_retreat')]
	public function retreat(): Response {
		[$char, $transit] = $this->gk->gateway('dungeon_retreat');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		# If we don't have an error, this is a legitimate move.
		$this->dm->moveCharacter($char, $transit);
		return $this->redirectToRoute('dungeon_status');
	}

	#[Route('/dungeon/attack/{target}', name: 'dungeon_attack', requirements: ['trasnit'=>'\d+'])]
	public function attack($target): Response {
		$char = $this->gk->gateway('dungeon_attack', ['target'=>$target->getId()]);
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		# If we don't have an error, this is a legitimate move.



		$this->em->flush();
		return $this->redirectToRoute('dungeon_status');
	}
}
