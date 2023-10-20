<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class UserLog {
	private ?\DateTimeInterface $ts = null;
	private ?string $ip = null;
	private ?string $route = null;
	private ?string $agent = null;
	private ?int $id = null;
	private ?User $user = null;
	private ?string $slugs = null;
	private ?Character $character = null;

	public function getTs(): ?\DateTimeInterface {
   		return $this->ts;
   	}

	public function setTs(\DateTimeInterface $ts): static {
   		$this->ts = $ts;
   
   		return $this;
   	}

	public function getIp(): ?string {
   		return $this->ip;
   	}

	public function setIp(string $ip): static {
   		$this->ip = $ip;
   
   		return $this;
   	}

	public function getRoute(): ?string {
   		return $this->route;
   	}

	public function setRoute(string $route): static {
   		$this->route = $route;
   
   		return $this;
   	}

	public function getAgent(): ?string {
   		return $this->agent;
   	}

	public function setAgent(string $agent): static {
   		$this->agent = $agent;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	public function getUser(): ?User {
   		return $this->user;
   	}

	public function setUser(?User $user): static {
   		$this->user = $user;
   
   		return $this;
   	}

	public function getSlugs(): ?string {
   		return $this->slugs;
   	}

	public function setSlugs(string $slugs): static {
   		$this->slugs = $slugs;
   
   		return $this;
   	}

	public function getCharacter(): ?Character {
   		return $this->character;
   	}

	public function setCharacter(?Character $character): static {
   		$this->character = $character;
   
   		return $this;
   	}
}
