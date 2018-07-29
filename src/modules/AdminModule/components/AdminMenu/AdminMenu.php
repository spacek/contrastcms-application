<?php

namespace ContrastCms\Application\AdminModule;

use ContrastCms\Application\ModuleRepository;
use Nette;

final class AdminMenu extends Nette\Application\UI\Control
{
	public $repository = false;

	public function __construct(ModuleRepository $moduleRepository)
	{
		$this->repository = $moduleRepository;
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/AdminMenu.latte');
		$template->modules = $this->repository->getTopMenu();
		$template->moduleRepository = $this->repository;
		$template->render();
	}
}