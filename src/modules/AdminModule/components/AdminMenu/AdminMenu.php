<?php

namespace ContrastCms\Application\AdminModule;

use ContrastCms\Application\ModuleRepository;
use Nette;

final class AdminMenu extends Nette\Application\UI\Control
{
	public $repository = false;
	public $user = null;

	public function __construct(ModuleRepository $moduleRepository, ?Nette\Security\User $user = null)
	{
		$this->repository = $moduleRepository;
		$this->user = $user;
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/AdminMenu.latte');
		$template->modules = $this->repository->getTopMenu($this->user);
		$template->moduleRepository = $this->repository;
		$template->user = $this->user;
		$template->render();
	}
}
