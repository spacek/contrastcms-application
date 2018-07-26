<?php

namespace ContrastCms\Application\AdminModule;

class SettingPresenter extends SecuredPresenter
{

	public function actionDefault() {
		$this->template->setFile(__DIR__ . "/../templates/Setting/default.latte");
	}

	protected function createComponentSettingForm()
	{
		$form = new SettingForm();
		$form->onSuccess[] = [$this, "processSettingForm"];

		$form->setDefaults($this->contrastOptions);
		return $form;
	}

	public function processSettingForm(SettingForm $form)
	{
		$values = $form->getValues();

		// Do query
		$result = $this->context->getService("optionRepository")->change((array)$values, $this->lang);

		if ($result) {
			$this->flashMessage('Nastavení bylo úspěšně změněno');
		} else {
			$this->flashMessage('Nastavení se nepodařilo uložit, nebo nedošlo k žádné změně.');
		}

		$this->redirect("Setting:default");
	}
}