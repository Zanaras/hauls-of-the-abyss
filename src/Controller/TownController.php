<?php

namespace App\Controller;

use App\Entity\GuideKeeper;
use App\Service\GateKeeper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TownController extends AbstractController {

	private EntityManagerInterface $em;
	private GateKeeper $gk;
	private TranslatorInterface $trans;

	public function __construct(EntityManagerInterface $em, GateKeeper $gk, TranslatorInterface $trans) {
		$this->em = $em;
		$this->gk = $gk;
		$this->trans = $trans;
	}
	#[Route('/town', name: 'town_status')]
	public function index(): RedirectResponse|Response {
		$char = $this->gk->gateway('town_status');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		return $this->render('town/index.html.twig', [
			'options' => $this->gk->townNav($char),
		]);
	}

	#[Route('/town/toDungeon',name: 'town_to_dungeon')]
	public function townToDungeon(): RedirectResponse {
		$char = $this->gk->gateway('town_to_dungeon');
		if ($char instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($char->getReason(), [], 'gatekeeper'));
			return $this->redirectToRoute($char->getRoute());
		}
		$char->setAreaCode($this->gk::DUNGEON);
		$this->em->flush();
		return $this->redirectToRoute('dungeon_status');
	}
}
