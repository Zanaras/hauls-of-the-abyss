<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\GuideKeeper;
use App\Entity\NetExit;
use App\Entity\User;
use App\Entity\UserLog;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;


class AppState {
	private EntityManagerInterface $em;
	private Security $security;

	const USER = 'u';
	const CHAR = 'c';

	public function __construct(EntityManagerInterface $em, Security $security) {
		$this->em = $em;
		$this->security = $security;
	}

	public function security(string $return, string $route, array $slugs, bool $override = false, bool $flush = true): User|Character|GuideKeeper {
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
		throw new LogicException("Symfony Firewall Failure -- Route requires User but not authenticated.", 401);
	}

	public function generateToken($length = 128, $method = 'trimbase64'): string {
		$token = match ($method) {
			default => rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '='),
		};
		return $token;
	}

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
	 * @param        $user
	 * @param string $route
	 * @param false  $alwaysLog
	 *
	 * @return void
	 */
	public function logUser(User $user, string $route, array $slugs = [], bool $flush = false): void {
		$ip = $this->findIp();
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if ($user->getIp() != $ip) {
			$user->setIp($ip);
		}
		if ($user->getAgent() != $agent) {
			$user->setAgent($agent);
		}
		$entry = new UserLog;
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
}
