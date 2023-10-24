<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\GuideKeeper;
use App\Form\CharacterCreatorType;
use App\Service\AppState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayController extends AbstractController {


	private AppState $app;
	private EntityManagerInterface $em;
	private TranslatorInterface $trans;

	public function __construct(AppState $app, EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->app = $app;
		$this->em = $em;
		$this->trans = $trans;
	}

	#[Route('/play/{char}', name: 'play_character', requirements: ['char'=>'\d+'])]
	public function playCharacter(Character $char): Response {
		$user = $this->app->security('u', 'play_character', ['character'=>$char->getId()], false, false);
		if ($user instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($user->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($user->getRoute());
		}
		if ($user->getCharacters()->contains($char)) {
			$this->em->flush(); # Flush here, so if this attempt is malicious, we can commit both at once.
			if ($char->isActive()) {
				$user->setCurrentCharacter($char);
				switch ($char->getAreaCode()) {
					case AppState::TOWN:
						return $this->redirectToRoute('town_status');
					case AppState::DUNGEON:
						return $this->redirectToRoute('dungeon_status');
					case AppState::WILDS:
						return $this->redirectToRoute('wilds_status');
					case AppState::MU:
					default:
						# Not fully started character, NOTE: 0 is loosely equal to null, and null is the default for this field.
						$char->setAreaCode(AppState::TOWN);
						$this->em->flush();
						return $this->redirectToRoute('town_status');
				}
			} else {
				$this->addFlash('error', $this->trans->trans('user.character.dead', [], 'gatekeeper'));
				return $this->redirectToRoute('user_characters');
			}
		} else {
			# Illicit access attempt, log and redirect.
			$this->app->logUser($user, 'play_character', ['character'=>$char->getId()], true, 'sl');
			$this->addFlash('erorr', $this->trans->trans('user.character.mismatch', [], 'gatekeeper'));
			return $this->redirectToRoute('user_characters');
		}

	}

	#[Route('/play/create', name: 'play_create_character')]
	public function createCharacter(): Response|RedirectResponse {
		$user = $this->app->security('u', 'play_create_character');
		if ($user instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($user->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($user->getRoute());
		}
		$form = $this->createForm(CharacterCreatorType::class, null, ['origins'=>$this->app->findAvailableOrigins($user)]);

		return $this->render('play/create.html.twig', [
			'form'=>$form->createView(),
		]);
	}
}
