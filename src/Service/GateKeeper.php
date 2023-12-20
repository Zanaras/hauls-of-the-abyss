<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\GuideKeeper;
use Doctrine\ORM\EntityManagerInterface;

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
	const DIRECTIONS = [
		'N'=>'North',
		'NE'=>'North East',
		'E'=>'East',
		'SE'=>'South East',
		'S'=>'South',
		'SW'=>'South West',
		'W'=>'West',
		'NW'=>'North West',
		'U'=>'Up a Floor',
		'D'=>'Down a Floor',
	];
	private AppState $app;

	public function __construct(AppState $app) {
		$this->app = $app;
	}

	/**
	 * All routes that require a Character shoudl call this function and check if it returns an instance of a GudieKeeper object.
	 * GuideKeepers have two properties, a route (getRoute) to redirect the user to, and an error (getReason) to throw as a translated flash.
	 *
	 * @param string $route REQUIRED: Route to check, used as a dynamic function call. A route_test function must exist in GateKeeper for each route that is tested.
	 * @param array  $slugs OPTIONAL: Any slugs passed to the route, like a character ID or room ID. Pass in ['slug_use'=>'slug_provided']. For character IDs, an example: ['character'=>$character->getId()].
	 * @param array  $opts  OPTIONAL: Any flags you want to pass, like objects that Symfony's Router has already loaded or a key to tell the gateway to return an array instead of a Character.
	 * @param bool   $flush OPTIONAL: Allows you to tell GateKeeper to not immediately flush user logs to the DB. Unless you know your route has to do something with the database immediately upon load by a user, leave this alone.
	 *
	 * @return GuideKeeper|Character
	 */
	public function gateway(string $route, array $slugs = [], array $opts = ['list'=>true], bool $flush = true): GuideKeeper|Character|array {
		$user = $this->app->security($route, $slugs, false, $flush);
		if ($user instanceof GuideKeeper) {
			return $user;
		}
		$char = $this->checkAreaCode($user->getCurrentCharacter(), $route);
		if ($char instanceof GuideKeeper) {
			return $char;
		}
		/*
		 * $this->{$route._'test'}($char, $route, $slugs) is a dynamic function call.
		 * For example, if $route is "dungeon_move", then this would effectively be:
		 * 	$this->dungeon_move_test($char, $route, $slugs);
		 *
		 * Related, yes, this means we will be commonly overloading functions, as *many* routes don't need slugs.
		 */
		$test = $this->{$route . '_test'}($char, $route, $opts);
		if (array_key_exists('list', $test)) {
			return $test['list'];
		} elseif (array_key_exists('url', $test)) {
			return $char;
		} else {
			return new GuideKeeper($this->findSafeRoute($char), $test['description']);
		}
	}

	/**
	 *
	 * @param Character $char
	 * @param           $route
	 *
	 * @return GuideKeeper|Character
	 */
	private function checkAreaCode(Character $char, $route): GuideKeeper|Character {
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

	/**
	 * Determine the safest route to redirect a user to based on what condition the user (or more accruately, their character) is in within the game.
	 *
	 * @param Character $char
	 *
	 * @return string
	 */
	private function findSafeRoute(Character $char): string {
		/*
		 * The argument could be made that MU should redirect to character start, but if we've ended up using this the user is trying to be somewhere they shouldn't be.
		 * So we kick them back to a known safer spot, the user characters route, which resets their active character.
		 */
		return match ($char->getAreaCode()) {
			self::DUNGEON => 'dungeon_status',
			self::TOWN => 'town_status',
			self::WILDS => 'wilds_status',
			self::MU => 'user_characters',
		};
	}

	/**
	 * Build test pass array from inputs.
	 *
	 * @param string $title
	 * @param string $route
	 *
	 * @return array
	 */
	private function pass(string $title, string $route, array $params=[]): array {
		return [
			'name' => $title,
			'url' => $route,
			'parameters'=>$params,
		];
	}

	/**
	 * Build test fail array from inputs.
	 *
	 * @param string $title
	 * @param string $error
	 *
	 * @return array
	 */
	private function fail(string $title, string $error): array {
		return [
			'name' => $title,
			'description' => $error,
		];
	}

	/*
	 * Navigation Menu Functions
	 */

	public function townNav(Character $char, array $opts = []): array {
		$options = [];
		$options[] = $this->town_status_test($char);
		$options[] = $this->town_to_dungeon_test($char);
		return $options;
	}

	public function dungeonNav(Character $char, array $opts = []): array {
		$options = [];
		$options[] = $this->dungeon_status_test($char);
		$options[] = $this->dungeon_enter_test($char);
		$options[] = $this->dungeon_exit_test($char);
		$options[] = $this->dungeon_retreat_test($char);
		if ($room = $char->getRoom()) {
			foreach ($room->getExits() as $exit) {
				$options[] = $this->dungeon_move_test($char, 'dungeon_move', ['dir'=>$exit->getDirection(), 'transit'=>$exit]);
			}
		}
		return $options;
	}

	public function wildsNav(Character $char, array $opts = []): array {
		$options = [];
		return $options;
	}

	/*
	 * Route Security Checks
	 *
	 * These exist both to check that we're allowed to be at this route by checking things that would fail us from being here.
	 * If you want to condense or group multiple checks across many, turn them into a function, and then have that function return false if they can be there, and a string'd error code if they can't.
	 */

	/**
	 * Route security for "dungeon_status".
	 * @param Character $char
	 * @param string    $route
	 *
	 * @return array
	 */
	private function dungeon_status_test(Character $char, string $route = 'dungeon_status'): array {
		$title = 'Character Status';
		if ($char->getAreaCode() === self::MU) {
			return $this->fail($title, 'generic.notstarted');
		}
		if ($char->getAreaCode() !== self::DUNGEON) {
			return $this->fail($title, 'generic.notindungeon');
		}
		return $this->pass($title, $route);
	}

	private function dungeon_enter_test(Character $char, string $route = 'dungeon_enter'): array {
		$title = 'Enter the Abyss';
		if ($char->getAreaCode() === self::MU) {
			return $this->fail($title, 'generic.notstarted');
		}
		if ($char->getAreaCode() === self::DUNGEON && $char->getRoom()) {
			return $this->fail($title, 'dungeon.alreadyin');
		}
		return $this->pass($title, $route);
	}

	private function dungeon_exit_test(Character $char, string $route = 'dungeon_exit'): array {
		$title = 'Exit the Abyss';
		if ($char->getAreaCode() !== self::DUNGEON) {
			return $this->fail($title, 'generic.notindungeon');
		}
		if (!$char->getRoom()) {
			return $this->fail($title, 'dungeon.notexploring');
		}
		return $this->pass($title, $route);
	}

	private function dungeon_move_test(Character $char, string $route = 'dungeon_move', array $opts = []): array {
		if (!array_key_exists('dir', $opts)) {
			return $this->fail('Move to a Room', 'dungeon.nodirectiongiven');
		} else {
			$title = 'Move '.self::DIRECTIONS[$opts['dir']];
		}
		if ($char->getAreaCode() === self::MU) {
			return $this->fail($title, 'generic.notstarted');
		}
		if ($char->getAreaCode() !== self::DUNGEON) {
			return $this->fail($title, 'generic.notindungeon');
		}
		$room = $char->getRoom();
		if (!$room) {
			return $this->fail($title, 'dungeon.notexploring');
		}
		foreach ($room->getExits() as $exit) {
			if ($exit->getDirection() === $opts['dir']) {
				$return = $this->pass($title, $route, [
					'dir'=>$opts['dir']
				]);
				$return['list'] = [$char, $exit];
				return $return;
			}
		}
		return $this->fail($title, 'dungeon.notvalidtransit');
	}

	private function dungeon_retreat_test(Character $char, string $route = 'dungeon_status'): array {
		$title = 'Retreat from the Room';
		if ($char->getAreaCode() === self::MU) {
			return $this->fail($title, 'generic.notstarted');
		}
		if ($char->getAreaCode() !== self::DUNGEON) {
			return $this->fail($title, 'generic.notindungeon');
		}
		$room = $char->getRoom();
		if (!$room) {
			return $this->fail($title, 'dungeon.notexploring');
		}
		$last = $char->getLastRoom();
		if (!$last) {
			return $this->fail($title, 'dungeon.nolastroom');
		}
		foreach ($room->getExits() as $each) {
			if ($each->getToRoom() === $last) {
				$return = $this->pass($title, $route);
				$return['list'] = [$char, $each];
				return $return;
			}
		}
		return $this->fail($title, 'dungeon.noreturnpath');
	}

	/**
	 * Route security check for "town_status".
	 * @param Character $char
	 * @param string    $route
	 *
	 * @return array
	 */
	private function town_status_test(Character $char, string $route = 'town_status'): array {
		$title = 'Character Status';
		if ($char->getAreaCode() === self::MU) {
			return $this->fail($title, 'generic.notstarted');
		}
		if ($char->getAreaCode() === self::DUNGEON) {
			return $this->fail($title, 'generic.indungeon');
		}
		return $this->pass($title, $route);
	}

	/**
	 * Route security check for "town_to_dungeon".
	 *
	 * @param Character $char
	 * @param string    $route
	 *
	 * @return array
	 */
	private function town_to_dungeon_test(Character $char, string $route = 'town_to_dungeon'): array {
		$title = 'Enter the Abyss';
		if ($char->getAreaCode() === self::MU) {
			return $this->fail($title, 'generic.notstarted');
		}
		if ($char->getAreaCode() === self::DUNGEON) {
			return $this->fail($title, 'generic.indungeon');
		}
		return $this->pass($title, $route);
	}
}
