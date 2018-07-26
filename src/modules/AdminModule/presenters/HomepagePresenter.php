<?php

namespace ContrastCms\Application\AdminModule;

class HomepagePresenter extends SecuredPresenter
{

	public function actionDefault()
	{
		$this->template->setFile(__DIR__ . "/../templates/Homepage/default.latte");
	}

	public function actionLogout()
	{

	}

	public function actionSearch()
	{
		$query = $this->getParameter("q");
		$results = $this->context->getService("postRepository")->search($query);
		$this->template->results = $results;
	}

}