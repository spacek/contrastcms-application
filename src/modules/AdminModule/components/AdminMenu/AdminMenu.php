<?php

namespace ContrastCms\Application\AdminModule;
use Nette;

final class AdminMenu extends Nette\Application\UI\Control
{
	public $modules = false;

	public function setModules($modules)
	{
		if ($modules) {
			$this->modules = $modules;
		}
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/AdminMenu.latte');
		$template->modules = $this->modules;
		$template->render();
	}
}