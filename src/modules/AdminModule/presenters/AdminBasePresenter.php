<?php

namespace ContrastCms\Application\AdminModule;

use Contrast\Authenticator;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;

abstract class AdminBasePresenter extends \BasePresenter
{

	public $database;
	public $sessionData;

	/** @persistent */
	public $lang;

	public function startup()
	{
		parent::startup();

        $ips = [
			"127.0.0.1",
			"::1",
        ];

        if(!in_array($_SERVER["REMOTE_ADDR"], $ips, true)) {
            //$this->redirect(":Homepage:default");
        }

		$this->user->setAuthenticator($this->context->getService("authenticator"));

		if($this->configParams['application']['cms']['show_tree'] == true) {
			$this->setLayout('layoutLoggedWithTree');
		} else {
			$this->setLayout('layoutLogged');
		}

		$this->sessionData = $this->getSession('admin');
	}


	// Compontents

    protected function createComponentLeftTree()
    {
        $menu = \TreeProvider::getTreeObject($this->context->getService("postRepository"), $this->lang);

        $slider = new LeftTree();
        $slider->setData($menu);
        return $slider;
    }

	protected function createComponentSuperadminSidebar()
    {
        $slider = new SuperadminSidebar();

        return $slider;

	    if(in_array($this->getUser()->getIdentity()->username, array("superadmin", "hudzikova", "admin", "kuba", "klient", "monika", "editor", "ligs"))) {

            // Connect to superadmin database
            $params = $this->context->getParameters();
            $mainDatabaseConnection = new Connection("mysql:host=localhost;dbname=contrastcms", "contrastcms", "hZPLxRNUqBST8L7c");
            $structure = new Structure($mainDatabaseConnection, new DevNullStorage());
            $mainDatabase = new Context($mainDatabaseConnection, $structure);
            $websites = $mainDatabase->table("website")->where("group = ?", "LIGS")->fetchAll();

            $availableWebsites = array();
            foreach($websites as $website) {
                $item = new \stdClass();
                $item->name = $website->name;
                $item->url  = $website->url . "/admin/sign/external/" . Authenticator::calculateExternalHash();
                $availableWebsites[] = $item;
            }

            $slider->setData($availableWebsites);

	    }

        return $slider;
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