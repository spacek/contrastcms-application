<?php

namespace ContrastCms\Application\AdminModule;

class HomepagePresenter extends SecuredPresenter
{

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