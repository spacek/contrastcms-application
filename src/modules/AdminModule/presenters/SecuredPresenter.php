<?php

namespace ContrastCms\Application\AdminModule;

abstract class SecuredPresenter extends AdminBasePresenter
{

	public function startup()
	{
		parent::startup();

		if (!($this->user->isLoggedIn()) || (!$this->user->isInRole('admin'))) {
			$this->redirect('Sign:in');
		}
	}

}