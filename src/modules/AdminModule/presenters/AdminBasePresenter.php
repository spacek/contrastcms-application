<?php

namespace ContrastCms\Application\AdminModule;

abstract class AdminBasePresenter extends \BasePresenter
{

	public $database;
	public $sessionData;

	/** @persistent */
	public $lang;

	public function startup()
	{
		parent::startup();

		$this->user->setAuthenticator($this->context->getService("authenticator"));
		$this->setLayout(__DIR__ . '/../templates/layoutLogged');
		$this->sessionData = $this->getSession('admin');
	}


	protected function createComponentAdminMenu()
	{

		$modulesProvider = $this->context->getService("moduleRepository");
		$modules = $modulesProvider->getTopMenu();

		$menu = new AdminMenu();
		$menu->setModules($modules);
		return $menu;
	}

}