<?php

namespace ContrastCms\Application;

class ModuleRepository extends Repository
{
	public function getEnabled()
	{
		$modules = $this->findBy(array('enabled' => 1), 'id ASC');

		if ($modules) {
			return $modules;
		}

		return array();
	}

	public function getTopMenu($parentId = 0)
	{
		$modules = $this->findBy(array('enabled' => 1, 'in_menu' => 1, 'parent_id' => $parentId), 'id ASC');

		if ($modules) {
			return $modules;
		}

		return array();
	}
}