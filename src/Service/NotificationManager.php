<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Journal;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationManager {

	protected EntityManagerInterface $em;
	protected TranslatorInterface $trans;
	protected DiscordIntegrator $discord;

	public function __construct(EntityManagerInterface $em, TranslatorInterface $trans, DiscordIntegrator $discord) {
		$this->em = $em;
		$this->trans = $trans;
		$this->discord = $discord;
	}

	public function spoolAchievement($type, Character $char): void {
		if ($type === 'dragon') {
			$text = '['.$char->getName().'](https://mightandfealty.com/character/view/'.$char->getId().') has accomplished a feat few others have, and successfully slain a dragon!';
			try {
				$this->discord->pushToGeneral($text);
			} catch (Exception $e) {
				# Nothing
			}
		}
	}

	public function spoolJournal(Journal $journal): void {
		$text = '['.$journal->getCharacter()->getName().'](https://haulsoftheabyss.com/character/view/'.$journal->getCharacter()->getId().') has written ['.$journal->getTopic().'](https://haulsoftheabyss.com/journal/'.$journal->getId().').';
		try {
			$this->discord->pushToGeneral($text);
		} catch (\Exception $e) {
			# Nothing
		}
	}

}
