<?php

namespace ContrastCms\Application\AdminModule;

use ContrastCms\VisualPaginator\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Security\Passwords;

class UserPresenter extends SecuredPresenter
{
	public function actionDefault()
	{

		$session = $this->context->getService("session");
		$filter = $session->getSection("filter-user");
		if (!$filter->limit) {
			$filter->limit = 1000;
		}
		$this->template->limit = $filter->limit;

		$vp = new VisualPaginator();
		$vp->loadState($this->request->getParameters());
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $filter->limit;
		$paginator->itemCount = $this->context->getService("userRepository")->count();

		$this->template->results = $this->context->getService("userRepository")->find()->limit($paginator->itemsPerPage, $paginator->offset);

		$this->addComponent($vp, "vp");

		$this->template->setFile(__DIR__ . "/../templates/User/default.latte");
	}

	public function actionEdit($id)
	{
		$this["userForm"]["type"]->setValue("edit");
		$this["userForm"]["id"]->setValue($id);

		// Load other data

		$item = $this->context->getService("userRepository")->findById($id);
		$record = $item->fetch();

		if (!$record) {
			throw new BadRequestException("404");
		}

		// Populate

		$this['userForm']->setDefaults($record);
		$this["userForm"]->setDefaults(array("password" => ""));

		$this->template->setFile(__DIR__ . "/../templates/User/edit.latte");
	}

	public function actionAdd()
	{
		$this["userForm"]["type"]->setValue("insert");
		$this->template->setFile(__DIR__ . "/../templates/User/add.latte");
	}

	// Form

	protected function createComponentUserForm()
	{
		$form = new UserForm(null, null, $this->roles);
		$form->onSuccess[] = [$this, "processUserForm"];
		return $form;
	}

	public function processUserForm(UserForm $form)
	{
		$values = $form->getValues();

		if ($values->type === "edit") {

			// Unset redudant fields
			$id = $values->id;
			unset($values->id);
			unset($values->type);

			if (trim($values->password) !== "") {
				$values->password = Passwords::hash($values->password);
			} else {
				unset($values->password);
			}

			// Do query
			$result = $this->context->getService("userRepository")->update((array)$values, $id);

			if ($result) {
				$this->flashMessage('Položka byla úspěšně upravena.');
			} else {
				$this->flashMessage('Položku se nepodařilo upravit, nebo nedošlo k žádné změně.');
			}

			$this->redirect("User:edit", $id);

		} else {
			// Unset redudant fields
			unset($values->id);
			unset($values->type);

			// Extend store array
			$values->created_at = date("Y-m-d H:i:s");

			if (trim($values->password) !== "") {
				$values->password = Passwords::hash($values->password);
			} else {
				unset($values->password);
			}

			// Do query
			$result = $this->context->getService("userRepository")->insert((array)$values);

			if ($result) {
				$this->flashMessage('Položka byla úspěšně přidána.');
				$this->redirect("User:edit", $result);
			} else {
				$this->flashMessage('Položku se nepodařilo přidat.');
				$this->redirect("User:default");
			}

		}
	}

	public function actionDelete($id)
	{
		$postRepository = $this->context->getService("userRepository");
		$postRepository->deleteById($id);
		$this->redirectUrl($_SERVER['HTTP_REFERER']);
		exit;
	}
}