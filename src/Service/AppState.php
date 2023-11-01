<?php

namespace App\Service;

use App\Entity\AppSetting;
use App\Entity\Character;
use App\Entity\GuideKeeper;
use App\Entity\NetExit;
use App\Entity\Origin;
use App\Entity\SecurityLog;
use App\Entity\User;
use App\Entity\UserLog;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * AppState is all the bare essential functions that are expected to be called just about anywhere.
 */
class AppState {

	# Constants for user return flags.
	const USER = 'u';
	const CHAR = 'c';

	# Constants for Character AreaCodes.
	const MU = 0;
	const TOWN = 1;
	const DUNGEON = 2;
	const WILDS = 3;

	const SETTINGTYPES = ['string', 'bool', 'int', 'float'];

	private EntityManagerInterface $em;
	private Security $security;

	public function __construct(EntityManagerInterface $em, Security $security) {
		$this->em = $em;
		$this->security = $security;
	}

	/**
	 * Fetch a setting from the game's setting database table, optionally with a default value if the setting hasn't been formally set elsewhere.
	 *
	 * @param string 			$name
	 * @param string|int|float|bool   	$default
	 *
	 * @return string|int|float|bool
	 */
	public function getGlobal(string $name, string|int|float|bool $default=false) {
		$setting = $this->em->getRepository(AppSetting::class)->findOneBy(['name'=>$name]);
		if (!$setting) return $default;
		return match ($setting->getType()) {
			'int' => (int)$setting->getValue(),
			'float' => (float)$setting->getvalue(),
			'bool' => (bool)$setting->getValue(),
			default => (string)$setting->getValue(),
		};
	}

	/**
	 * Commits a global setting to the database, creating a new entry if necessary.
	 *
	 * @param string                $name	Name of the setting
	 * @param string|int|float|bool $value  Value of the setting
	 * @param string                $type   Variable type of the setting, used in type-casting the return value.
	 *
	 * @return AppSetting		Object version of the setting you just set.
	 */
	public function setGlobal(string $name, string|int|float|bool $value, string $type = 'string'): AppSetting {
		if (in_array($type, $this::SETTINGTYPES)) {
			$setting = $this->em->getRepository(AppSetting::class)->findOneBy(['name'=>$name]);
			if (!$setting) {
				$setting = new AppSetting();
				$setting->setName($name);
				$setting->setType($type);
				$this->em->persist($setting);
			}
			$setting->setValue($value);
			$this->em->flush($setting);
			return $setting;
		} else {
			throw new \LogicException("Bad global type set. Must be 'string', 'int', 'float', or 'bool'.");
		}

	}

	/**
	 * fetchGlobal both checks and returns a global that is set, but also creates and returns it if is not.
	 * Slightly slower than getGlobal, but probably not noticeable to users.
	 * @param string                $name		Name of the global setting
	 * @param string|int|float|bool $default	Default value of the setting -- overridden if the setting already exists.
	 * @param string                $type		Type of the value that should be returned.
	 *
	 * @return float|bool|int|string
	 */
	public function fetchGlobal(string $name, string|int|float|bool $default, string $type = 'string'): float|bool|int|string {
		if (in_array($type, $this::SETTINGTYPES)) {
			$setting = $this->em->getRepository(AppSetting::class)->findOneBy(['name'=>$name]);
			if (!$setting) {
				$setting = new AppSetting;
				$setting->setName($name);
				$setting->setType($type);
				$setting->setValue($default);
				$this->em->persist($setting);
				$this->em->flush();
			}
			return match ($setting->getType()) {
				'int' => (int)$setting->getValue(),
				'float' => (float)$setting->getvalue(),
				'bool' => (bool)$setting->getValue(),
				default => (string)$setting->getValue(),
			};
		} else {
			throw new \LogicException("Bad global type set. Must be 'string', 'int', 'float', or 'bool'.");
		}
	}

	/**
	 * The user function does all the basic access checks for an authenticated user on routes we either want to log or need to perform second and third level checks on.
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
	public function security(string $route, array $slugs = [], bool $override = false, bool $flush = true): GuideKeeper|User|Character|null {
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
			return $user;
		}
		return $user;
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
		return match ($method) {
			default => rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '='),
		};
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
	public function generateAndCheckToken($length, $check = 'User', $against = 'resetToken'): bool|string {
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

	public function findAvailableOrigins(User $user) {
		$all = new ArrayCollection();
		$public = $this->em->getRepository(Origin::class)->findBy(['public'=>true]);
		foreach ($public as $each) {
			$all->add($each);
		}
		foreach ($user->getUnlockedOrigins() as $each) {
			$all->add($each->getOrigin());
		}
		return $all;
	}

	public function checkCharacterLimit(User $user): array {
		$characters_allowed = $this->fetchGlobal('activeCharacters', 1, 'int');
		$characters_active = $user->getActiveCharacters()->count();
		if ($characters_active > $characters_allowed) {
			$make_more = false;
		} else {
			$make_more = true;
		}
		return [$make_more, $characters_active, $characters_allowed];
	}
}
