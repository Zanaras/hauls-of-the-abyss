<?php

namespace App\Entity;

class GuideKeeper {
	private string $route;
	private string $reason;

	public function __construct(string $route, string $reason) {
		$this->route = $route;
		$this->reason = $reason;
	}

	public function getRoute() {
		return $this->route;
	}

	public function getReason() {
		return $this->reason;
	}
}
