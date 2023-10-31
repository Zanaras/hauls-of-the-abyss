<?php

namespace App\Controller;

use App\Entity\UpdateNote;
use App\Entity\User;
use App\Form\UpdateNoteType;
use App\Service\AppState;
use App\Service\CommonService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GMController extends AbstractController {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}
	#[Route ('/gm', name:'maf_gm_pending')]
	public function pendingAction(): Response {
		$query = $this->em->createQuery('SELECT r from App\Entity\UserReport r WHERE r.actioned = false');
		$reports = $query->getResult();

		return $this->render('gm/pending.html.twig',  [
			'reports'=>$reports,
		]);
	}

	#[Route ('/gm/user/{id}', name:'maf_gm_user_reports')]
	public function userReportsAction(User $id): Response {
		return $this->render('gm/userReports.html.twig',  [
			'by'=>$id->getReports(),
			'against'=>$id->getReportsAgainst()
		]);
	}

	#[Route ('/gm/archive', name:'maf_gm_pending')]
	public function actionedAction(): Response {
		$query = $this->em->createQuery('SELECT r from App\Entity\UserReport r WHERE r.actioned = true');
		$reports = $query->getResult();

		return $this->render('gm/pending.html.twig',  [
			'reports'=>$reports,
		]);
	}

	#[Route ('/gm/update/{id}', name:'maf_admin_update')]
	#[Route ('/gm/update/')]
	#[Route ('/gm/update')]
	public function updateNoteAction(AppState $app, Request $request, UpdateNote $id=null): RedirectResponse|Response {
		if ($request->query->get('last')) {
			$id = $this->em->createQuery('SELECT n FROM App:UpdateNote n ORDER BY n.id DESC')->setMaxResults(1)->getSingleResult();
		}
		$form = $this->createForm(UpdateNoteType::class, null, ['note'=>$id]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if (!$id) {
				$note = new UpdateNote();
				$now = new DateTime('now');
				$note->setTs($now);
				$version = $data['version'];
				$note->setVersion($version);
				$this->em->persist($note);
			} else {
				$note = $id;
			}
			$note->setText($data['text']);
			$note->setTitle($data['title']);
			$this->em->flush();
			if (!$id) {
				$app->setGlobal('game-version', $version);
				$app->setGlobal('game-updated', $now->format('Y-m-d'));
			}
			$this->addFlash('notice', 'Update note created.');
			return $this->redirectToRoute('user_characters');
		}

		return $this->render('GM/update.html.twig', [
			'form'=>$form->createView()
		]);
	}
}
