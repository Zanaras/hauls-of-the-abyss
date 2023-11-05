<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\GuideKeeper;
use App\Form\CharacterCreatorType;
use App\Service\AppState;
use App\Service\DungeonMaster;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayController extends AbstractController {


	private AppState $app;
	private DungeonMaster $dm;
	private EntityManagerInterface $em;
	private TranslatorInterface $trans;

	public function __construct(AppState $app, DungeonMaster $dm, EntityManagerInterface $em, TranslatorInterface $trans) {
		$this->app = $app;
		$this->dm = $dm;
		$this->em = $em;
		$this->trans = $trans;
	}

	#[Route('/play/{char}', name: 'play_character', requirements: ['char'=>'\d+'])]
	public function playCharacter(Character $char): Response {
		$user = $this->app->security('play_character', ['character'=>$char->getId()], false, false);
		if ($user instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($user->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($user->getRoute());
		}
		if ($user->getCharacters()->contains($char)) {
			if ($char->isActive()) {
				$user->setCurrentCharacter($char);
				$char->setLastAccess(new DateTime('now'));
				$this->em->flush();
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
	public function createCharacter(Request $request): Response|RedirectResponse {
		$user = $this->app->security('play_create_character');
		if ($user instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($user->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($user->getRoute());
		}
		[$makeMore, $activeChars, $allowedChars] = $this->app->checkCharacterLimit($user);
		if (!$makeMore) {
			throw new AccessDeniedHttpException('newcharacter.overlimit');
		}
		$availableOrigins = $this->app->findAvailableOrigins($user);
		$form = $this->createForm(CharacterCreatorType::class, null, ['origins'=>$availableOrigins, 'races'=>$this->app->findAvailableRaces()]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if (!$availableOrigins->contains($data['origin'])) {
				$form->addError(new FormError('Selected origin not in list of available origins.'));
			} else {
				$char = new Character();
				$this->em->persist($char);
				$char->setUser($user);
				$char->setName($data['name']);
				$char->setGender($data['gender']);
				$char->setOrigin($data['origin']);
				$char->setRace($data['race']);
				$char->setAreaCode(AppState::TOWN);
				$char->setAlive(true);
				$char->setSlumbering(false);
				$char->setSpecial(false);
				$char->setWounded(0);
				$energy = $this->dm->calculateMaxEnergy($char);
				$char->setMaxEnergy($energy);
				$char->setEnergy($energy);
				$now = new DateTime("now");
				$char->setCreated($now);
				$char->setLastAccess($now);
				$this->em->flush();
				return $this->redirectToRoute('play_character', ['char'=>$char->getId()]);
			}
		}
		return $this->render('play/create.html.twig', [
			'form'=>$form->createView(),
			'makeMore'=>$makeMore,
			'activeChars'=>$activeChars,
			'allowedChars'=>$allowedChars,
		]);
	}
}
