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
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AppState is all the bare essential functions that are expected to be called just about anywhere.
 */
class AppState {

	# Constants for security return flags.
	const USER = 'u';
	const CHAR = 'c';

	# Constants for Character AreaCodes.
	const MU = 0;
	const TOWN = 1;
	const DUNGEON = 2;
	const WILDS = 3;

	private EntityManagerInterface $em;
	private Security $security;

	public function __construct(EntityManagerInterface $em, Security $security) {
		$this->em = $em;
		$this->security = $security;
	}

	/**
	 * The security function does all the basic access checks for an authenticated user on routes we either want to log or need to perform second and third level checks on.
	 * Checks all users if they are banned and, if exit checking applies, if they're using a trackable IP or not (so we can prevent multis).
	 *
	 * @param string $return
	 * @param string $route
	 * @param array  $slugs
	 * @param bool   $override
	 * @param bool   $flush
	 *
	 * @return User|Character|GuideKeeper|null
	 */
	public function security(string $return, string $route, array $slugs = [], bool $override = false, bool $flush = true): User|Character|GuideKeeper|null {
		$user = $this->security->getUser();
		/** @var User $user */
		if ($this->security->isGranted('IS_AUTHENTICATED')) {
			if ($route !== 'ip_needed') {
				if ($user->getBanned()) {
					$this->logUser($user, $route, $slugs, $flush);
					return new GuideKeeper('index', 'appState.ban.'.$user->getBanReason());
				}
				if (!$user->getBypassExitCheck() && $this->exitsCheck($user)) {
					return new GuideKeeper('need_ip', '');
				}
			}
			if ($override || $user->getWatched()) {
				$this->logUser($user, $route, $slugs, $flush);
			}
			if ($return === 'u') {
				return $user;
			} elseif ($return === 'c') {
				$char = $user->getCurrentCharacter();
				if ($char) {
					return $char;
				} else {
					return new GuideKeeper('user_characters', 'appState.noChar');
				}
			}
		}
		return null;
	}

	/**
	 * Returns a standardized and sanitized class name for an entity.
	 * @param $entity
	 *
	 * @return false|int|string
	 */
	public function getClassName($entity): false|int|string {
		$classname = get_class($entity);
		if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
		return $pos;
	}

	/**
	 * Generates a token that is, theoretically, somewhat cryptographically secure.
	 *
	 * @param $length
	 * @param $method
	 *
	 * @return string
	 * @throws Exception
	 */
	public function generateToken($length = 128, $method = 'trimbase64'): string {
		$token = match ($method) {
			default => rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '='),
		};
		return $token;
	}

	/**
	 * Generates and checks uniqueness of a token against a particular object and object property.
	 * If you only need a token, just call generateToken instead.
	 *
	 * @param $length
	 * @param $check
	 * @param $against
	 *
	 * @return bool|string
	 * @throws Exception
	 */
	public function generateAndCheckToken($length, $check = 'User', $against = 'reset_token'): bool|string {
		$valid = false;
		$token = false;
		$em = $this->em;
		if ($check == 'User') {
			while (!$valid) {
				$token = $this->generateToken($length, 'bin2hex');
				$result = $em->getRepository(User::class)->findOneBy([$against => $token]);
				if (!$result) {
					$valid = true;
				}
			}
		}
		return $token;
	}

	/**
	 * Returns a user's IP, usually as a string.
	 *
	 * @return mixed
	 */
	public function findIp(): mixed {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			//ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			//ip pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * Logs a given user if they have $user->getWatched set to true or if $alwaysLog is set to true.
	 *
	 * @param User   $user
	 * @param string $route
	 * @param array  $slugs
	 * @param bool   $flush
	 *
	 * @return void
	 */
	public function logUser(User $user, string $route, array $slugs = [], bool $flush = false, string $type = 'ul'): void {
		$ip = $this->findIp();
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if ($user->getIp() != $ip) {
			$user->setIp($ip);
		}
		if ($user->getAgent() != $agent) {
			$user->setAgent($agent);
		}
		if ($type !== 'ul') {
			$entry = new SecurityLog;
		} else {
			$entry = new UserLog;
		}
		$this->em->persist($entry);
		$entry->setTs(new DateTime('now'));
		$entry->setUser($user);
		$entry->setIp($ip);
		$entry->setAgent($agent);
		$entry->setRoute($route);
		$txt = '';
		foreach ($slugs as $slug => $var) {
			$txt .= $slug . ': ' . $var . '; ';
		}
		$entry->setSlugs($txt);
		if ($flush) {
			$this->em->flush();
		}
	}

	/**
	 * Checks whether a given user is accessing from an IP recorded on the NetExits table.
	 * Returns false if they have $user->getBypassExits() as true or if the IP isn't found in the NetExits table.
	 *
	 * @param $user
	 *
	 * @return bool
	 */
	public function exitsCheck($user): bool {
		if ($user->getBypassExits()) {
			# Trusted user. Check bypassed.
			return false;
		}
		$ip = $this->findIp();
		if ($this->em->getRepository(NetExit::class)->findOneBy(['ip' => $ip])) {
			# Hit found, check failed.
			return true;
		}
		# Nothing found, check passed.
		return false;
	}

	public function findAvailableOrigins(Character $user) {

	}
}
