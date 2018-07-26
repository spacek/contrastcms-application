<?php

namespace ContrastCms\Application\AdminModule;

use Contrast\Authenticator;

final class UserPresenter extends SecuredPresenter
{
    public function actionDefault() {

        $session = $this->context->getService("session");
        $filter = $session->getSection("filter-user");
        if(!$filter->limit) {
            $filter->limit = 10;
        }
        $this->template->limit = $filter->limit;

        $vp = new \VisualPaginator($this, 'vp');
        $vp->loadState($this->request->getParameters());
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $filter->limit;
        $paginator->itemCount = $this->context->getService("userRepository")->count();

        $this->template->results = $this->context->getService("userRepository")->fetch($paginator->offset, $paginator->itemsPerPage);
    }

    public function actionEditUser($id) {
        $this["userForm"]["type"]->setValue("edit");
        $this["userForm"]["id"]->setValue($id);

        // Load other data

        $item = $this->context->getService("userRepository")->findById($id);
        $record = $item->fetch();

        if (!$record) {
            throw new BadRequestException;
        }

        // Populate

        $this['userForm']->setDefaults($record);
        $this["userForm"]->setDefaults(array("password" => ""));
    }

    public function actionAddUser($id) {
        $this["userForm"]["type"]->setValue("insert");
        $this["userForm"]["id"]->setValue($id);
    }

    // Form

    protected function createComponentUserForm()
    {
        $form = new UserForm();
        $form->onSuccess[] = [$this, "processUserForm"];
        return $form;
    }

    public function processUserForm(UserForm $form)
    {
        $values = $form->getValues();

        if($values->type == "edit") {

            // Unset redudant fields
            $id = $values->id;
            unset($values->id);
            unset($values->type);

            if(trim($values->password) != "") {
                $values->password = Authenticator::calculateHash($values->password);
            } else {
                unset($values->password);
            }

            // Do query
            $result = $this->context->getService("userRepository")->update((array)$values, $id);

            if($result) {
                $this->flashMessage('Položka byla úspěšně upravena.');
            } else {
                $this->flashMessage('Položku se nepodařilo upravit, nebo nedošlo k žádné změně.');
            }

            $this->redirect("User:editUser", $id);

        } else {
            // Unset redudant fields
            unset($values->id);
            unset($values->type);

            // Extend store array
            $values->created_at = date("Y-m-d H:i:s");

            if(trim($values->password) != "") {
                $values->password = Authenticator::calculateHash($values->password);
            } else {
                unset($values->password);
            }

            // Do query
            $result = $this->context->getService("userRepository")->insert((array)$values);

            if($result) {
                $this->flashMessage('Položka byla úspěšně přidána.');
                $this->redirect("User:editUser", $result);
            } else {
                $this->flashMessage('Položku se nepodařilo přidat.');
                $this->redirect("User:default");
            }

        }
    }

    public function actionDeleteUser($id) {

        // todo check permission

        $postRepository = $this->context->getService("userRepository");
        $postRepository->deleteById($id); // todo: do recursive delete

        $this->redirectUrl($_SERVER['HTTP_REFERER']);
        exit;
    }
}