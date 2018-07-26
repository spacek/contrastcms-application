<?php

namespace ContrastCms\Application;

class ModuleRepository extends Repository
{
	public function getEnabled()
	{
		$modules = $this->findBy(array("enabled" => 1), "id ASC");

		if ($modules) {
			return $modules;
		}

		return array();
	}

	public function getTopMenu()
	{
		$modules = $this->findBy(array("enabled" => 1, "in_menu" => 1), "id ASC");

		if ($modules) {
			return $modules;
		}

		return array();
	}
}