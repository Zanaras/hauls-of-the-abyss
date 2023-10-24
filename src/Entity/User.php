<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
	private ?int $id = null;
	private ?string $username = null;
	private ?string $password = null;
	private ?string $ip;
	private ?string $email = null;
	private ?bool $public = null;
	private ?bool $newsletter = null;
	private ?bool $notifications = null;
	private ?string $notificationTarget = null;
	private ?bool $watched = null;
	private ?bool $bypassExitCheck = null;
	private ?DateTime $created = null;
	private ?DateTime $lastPasswordChange = null;
	private ?DateTime $lastLogin = null;
	private array $roles = [];
	private ?string $agent;
	private ?bool $enabled = null;
	private ?string $language = null;
	private Collection $logs;
	private bool $isVerified = false;
	private ?bool $banned = null;
	private ?string $banReason = null;
	private Collection $characters;
	private ?Character $currentCharacter = null;
	private Collection $securityLogs;
	private Collection $unlockedOrigins;
	private Collection $descriptions;
	private ?Description $description = null;

	public function __construct() {
   		$this->logs = new ArrayCollection();
   		$this->characters = new ArrayCollection();
   		$this->securityLogs = new ArrayCollection();
   		$this->unlockedOrigins = new ArrayCollection();
   		$this->descriptions = new ArrayCollection();
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	public function getUsername(): ?string {
   		return $this->username;
   	}

	public function setUsername(string $username): static {
   		$this->username = $username;
   		return $this;
   	}

	public function getEmail(): ?string {
   		return $this->email;
   	}

	public function setEmail(string $email) {
   		$this->email = $email;
   		return $this;
   	}

	public function getPublic(): ?bool {
   		return $this->public;
   	}

	public function setPublic(bool $public) {
   		$this->public = $public;
   		return $this;
   	}

	public function getNewsletter(): ?bool {
   		return $this->newsletter;
   	}

	public function setNewsletter(bool $newsletter) {
   		$this->newsletter = $newsletter;
   		return $this;
   	}

	public function getNotificationTarget(): ?string {
   		return $this->notificationTarget;
   	}

	public function setNotificationTarget(string $target) {
   		$this->notificationTarget = $target;
   		return $this;
   	}

	public function getWatched(): ?bool {
   		return $this->watched;
   	}

	public function setWatched(bool $watched) {
   		$this->watched = $watched;
   		return $this;
   	}

	public function getBypassExitCheck(): ?bool {
   		return $this->bypassExitCheck;
   	}

	public function setBypassExitCheck(bool $check) {
   		$this->bypassExitCheck = $check;
   		return $this;
   	}

	public function getLastLogin(): DateTime {
   		return $this->lastLogin;
   	}

	public function setLastLogin(?DateTime $when) {
   		if (!$when) {
   			$when = new DateTime("now");
   		}
   		$this->lastLogin = $when;
   		return $this;
   	}

	public function getLastPasswordChange(): DateTime {
   		return $this->lastPasswordChange;
   	}

	public function setLastPasswordChange(?DateTime $when) {
   		if (!$when) {
   			$when = new DateTime('now');
   		}
   		$this->lastPasswordChange = $when;
   		return $this;
   	}

	public function getIp(): ?string {
   		return $this->ip;
   	}

	public function setIp(string $ip) {
   		$this->ip = $ip;
   		return $this;
   	}

	public function getAgent(): ?string {
   		return $this->agent;
   	}

	public function setAgent(string $agent) {
   		$this->agent = $agent;
   		return $this;
   	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string {
   		return (string)$this->username;
   	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array {
   		$roles = $this->roles;
   		// guarantee every user at least has ROLE_USER
   		$roles[] = 'ROLE_USER';
   
   		return array_unique($roles);
   	}

	public function setRoles(array $roles): static {
   		$this->roles = $roles;
   
   		return $this;
   	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): string {
   		return $this->password;
   	}

	public function setPassword(string $password): static {
   		$this->password = $password;
   
   		return $this;
   	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials(): void {
   		// If you store any temporary, sensitive data on the user, clear it here
   		// $this->plainPassword = null;
   	}

	public function isEnabled(): ?bool {
   		return $this->enabled;
   	}

	public function setEnabled(bool $enabled): static {
   		$this->enabled = $enabled;
   
   		return $this;
   	}

	public function isWatched(): ?bool {
   		return $this->watched;
   	}

	public function isBypassExitCheck(): ?bool {
   		return $this->bypassExitCheck;
   	}

	public function getLanguage(): ?string {
   		return $this->language;
   	}

	public function setLanguage(?string $language): static {
   		$this->language = $language;
   
   		return $this;
   	}

	public function isNotifications(): ?bool {
   		return $this->notifications;
   	}

	public function setNotifications(?bool $notifications): static {
   		$this->notifications = $notifications;
   
   		return $this;
   	}

	public function isNewsletter(): ?bool {
   		return $this->newsletter;
   	}

	public function isPublic(): ?bool {
   		return $this->public;
   	}

	/**
	 * @return Collection<int, UserLog>
	 */
	public function getLogs(): Collection {
   		return $this->logs;
   	}

	public function addLog(UserLog $log): static {
   		if (!$this->logs->contains($log)) {
   			$this->logs->add($log);
   			$log->setUser($this);
   		}
   
   		return $this;
   	}

	public function removeLog(UserLog $log): static {
   		if ($this->logs->removeElement($log)) {
   			// set the owning side to null (unless already changed)
   			if ($log->getUser() === $this) {
   				$log->setUser(null);
   			}
   		}
   
   		return $this;
   	}

	public function isVerified(): bool {
   		return $this->isVerified;
   	}

	public function setIsVerified(bool $isVerified): static {
   		$this->isVerified = $isVerified;
   
   		return $this;
   	}

	public function isIsVerified(): ?bool {
   		return $this->isVerified;
   	}

	public function getBanned(): ?bool {
   		return $this->banned;
   	}

	public function setBanned(?bool $banned): static {
   		$this->banned = $banned;
   
   		return $this;
   	}

	public function getBanReason(): ?string {
   		return $this->banReason;
   	}

	public function setBanReason(?string $banReason): static {
   		$this->banReason = $banReason;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Character>
	 */
	public function getCharacters(): Collection {
   		return $this->characters;
   	}

	public function addCharacter(Character $character): static {
   		if (!$this->characters->contains($character)) {
   			$this->characters->add($character);
   			$character->setUser($this);
   		}
   
   		return $this;
   	}

	public function removeCharacter(Character $character): static {
   		if ($this->characters->removeElement($character)) {
   			// set the owning side to null (unless already changed)
   			if ($character->getUser() === $this) {
   				$character->setUser(null);
   			}
   		}
   
   		return $this;
   	}

	public function getCreated(): ?DateTimeInterface {
   		return $this->created;
   	}

	public function setCreated(?DateTimeInterface $created): static {
   		$this->created = $created;
   
   		return $this;
   	}

	public function isBanned(): ?bool {
   		return $this->banned;
   	}

	public function getCurrentCharacter(): ?Character {
   		return $this->currentCharacter;
   	}

	public function setCurrentCharacter(?Character $currentCharacter): static {
   		$this->currentCharacter = $currentCharacter;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, SecurityLog>
	 */
	public function getSecurityLogs(): Collection {
   		return $this->securityLogs;
   	}

	public function addSecurityLog(SecurityLog $securityLog): static {
   		if (!$this->securityLogs->contains($securityLog)) {
   			$this->securityLogs->add($securityLog);
   			$securityLog->setUser($this);
   		}
   
   		return $this;
   	}

	public function removeSecurityLog(SecurityLog $securityLog): static {
   		if ($this->securityLogs->removeElement($securityLog)) {
   			// set the owning side to null (unless already changed)
   			if ($securityLog->getUser() === $this) {
   				$securityLog->setUser(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, UserOrigin>
	 */
	public function getUnlockedOrigins(): Collection {
   		return $this->unlockedOrigins;
   	}

	public function addUnlockedOrigin(UserOrigin $unlockedOrigin): static {
   		if (!$this->unlockedOrigins->contains($unlockedOrigin)) {
   			$this->unlockedOrigins->add($unlockedOrigin);
   			$unlockedOrigin->setUser($this);
   		}
   
   		return $this;
   	}

	public function removeUnlockedOrigin(UserOrigin $unlockedOrigin): static {
   		if ($this->unlockedOrigins->removeElement($unlockedOrigin)) {
   			// set the owning side to null (unless already changed)
   			if ($unlockedOrigin->getUser() === $this) {
   				$unlockedOrigin->setUser(null);
   			}
   		}
   
   		return $this;
   	}

	public function getDescription(): ?Description {
   		return $this->description;
   	}

	public function setDescription(?Description $description): static {
   		// unset the owning side of the relation if necessary
   		if ($description === null && $this->description !== null) {
   			$this->description->setActiveUser(null);
   		}
   
   		// set the owning side of the relation if necessary
   		if ($description !== null && $description->getActiveUser() !== $this) {
   			$description->setActiveUser($this);
   		}
   
   		$this->description = $description;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Description>
	 */
	public function getDescriptions(): Collection {
   		return $this->descriptions;
   	}

	public function addDescription(Description $description): static {
   		if (!$this->descriptions->contains($description)) {
   			$this->descriptions->add($description);
   			$description->setUser($this);
   		}
   
   		return $this;
   	}

	public function removeDescription(Description $description): static {
   		if ($this->descriptions->removeElement($description)) {
   			// set the owning side to null (unless already changed)
   			if ($description->getUser() === $this) {
   				$description->setUser(null);
   			}
   		}
   
   		return $this;
   	}
}
