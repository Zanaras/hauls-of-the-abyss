<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Description;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * DescriptionManager is a one-stop-shop for adding Descriptions to Entities that support them.
 */
class DescriptionManager {

	private AppState $app;
	protected EntityManagerInterface $em;

	public function __construct(AppState $app, EntityManagerInterface $em) {
		$this->app = $app;
		$this->em = $em;
	}

	/**
	 * Handles creating new descriptions, updating active descriptions, and archiving previously active ones.
	 * Returns the new Description upon completion.
	 * @param Character|User $entity
	 * @param string         $text
	 * @param Character|null $character
	 *
	 * @return Description
	 */
	public function newDescription(Character|User $entity, string $text, Character $character=null): Description {
		/* First, check to see if there's already one. */
		$olddesc = NULL;
		if ($entity->getDescription()) {
			$olddesc = $entity->getDescription();
		}
		$eClass = $this->app->getClassName($entity);
		/* If we don't unset these and commit those changes, we create a unique key constraint violation when we commit the new ones. */
		if ($olddesc) {
			/* NOTE: If other things get descriptions, this needs updating with the new logic. */
			switch($eClass) {
				case 'Character':
					$olddesc->setActiveCharacter(NULL);
					$this->em->flush();
					break;
				case 'User':
					$olddesc->setActiveUser(NULL);
					$this->em->flush();
					break;
			}
		}

		$desc = new Description();
		$this->em->persist($desc);
		/* NOTE: If other things get descriptions, this needs updating with the new logic. */
		switch($eClass) {
			case 'Character':
				$desc->setActiveCharacter($entity);
				$desc->setCharacter($entity);
				break;
			case 'User':
				$desc->setActiveUser($entity);
				$desc->setUser($entity);
				break;
		}
		$entity->setDescription($desc);
		if ($olddesc) {
			$desc->setPrevious($olddesc);
		}
		$desc->setText($text);
		if ($character) {
			$desc->setUpdater($character);
		}
		$desc->setTs(new DateTime("now"));
		$this->em->flush();
		return $desc;
	}
}
