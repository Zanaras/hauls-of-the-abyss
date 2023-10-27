<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\GuideKeeper;
use App\Entity\NetExit;
use App\Entity\SecurityLog;
use App\Entity\User;
use App\Entity\UserLog;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * GateKeeper handles all in-character route user.
 * If Symfony firewall is Tier 1 of user, then AppState does Tier 2, and this does Tier 3.
 */
class GateKeeper {

	# Constants for Character AreaCodes.
	const MU = AppState::MU;
	const TOWN = AppState::TOWN;
	const DUNGEON = AppState::DUNGEON;
	const WILDS = AppState::WILDS;

	private EntityManagerInterface $em;
	private Security $security;

	private bool $areaMatched = false;

	public function __construct(AppState $app, EntityManagerInterface $em, Security $security) {
		$this->app = $app;
		$this->em = $em;
	}

	public function gateway(string $route, array $slugs = [], bool $flush = true) {
		$char = $this->app->security('c', $route, $slugs, false, $flush);
		if ($char instanceof GuideKeeper) {
			return $char;
		}
		$char = $this->checkAreaCode($char, $route);
		if ($char instanceof GuideKeeper) {
			return $char;
		}
		/*
		 * $this->{$route._'test'}($char, $route, $slugs) is a dynamic function call.
		 * For example, if $route is "dungeon_move", then this would effectively be:
		 * 	$this->dungeon_move_test($char, $route, $slugs);
		 */
		return $this->{$route.'_test'}($char, $route, $slugs);
	}

	private function checkAreaCode(Character $char, $route) {
		if (str_starts_with($route, 'town_')) {
			$area = self::TOWN;
		} elseif (str_starts_with($route, 'dungeon_')) {
			$area = self::DUNGEON;
		} elseif (str_starts_with($route, 'wilds_')) {
			$area = self::WILDS;
		} else {
			$area = self::MU;
		}
		if ($char->getAreaCode() === $area) {
			$this->areaMatched = true;
			return $char;
		}
		return $this->areaMismatch($char, $area);
	}

	private function areaMismatch(Character $char, int $area) {
		# TODO: cool stuff here with custom redirect messages, "You attempt to do something in the dungeon, but realize you're in the wilds" etc.
		if ($area === self::TOWN) {
			$route = 'town_status';
		} elseif ($area === self::DUNGEON) {
			$route = 'dungeon_status';
		} elseif ($area === self::WILDS) {
			$route = 'wilds_status';
		} else {
			$route = 'user_characters';
		}
		return new GuideKeeper($route, 'character.area.mismatch');
	}

	/*
	 * Navigation Menu Functions
	 */

	public function townNav(Character $char, array $opts = []) {

	}

	public function dungeonNav(Character $char, array $opts = []) {

	}

	public function wildsNav(Character $char, array $opts = []) {

	}

	/*
	 * Route Security Checks
	 */


}
