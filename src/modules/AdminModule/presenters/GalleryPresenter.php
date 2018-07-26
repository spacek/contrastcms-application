<?php

namespace ContrastCms\Application\AdminModule;

use ContrastCms\VisualPaginator\VisualPaginator;
use Nette\Application\BadRequestException;

final class GalleryPresenter extends SecuredPresenter
{
    public function actionDefault() {

        $session = $this->context->getService("session");
        $filter = $session->getSection("filter-user");
        if(!$filter->limit) {
            $filter->limit = 50;
        }

        $this->template->limit = $filter->limit;

        $vp = new VisualPaginator($this, 'vp');
        $vp->loadState($this->request->getParameters());
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $filter->limit;
        $paginator->itemCount = $this->context->getService("galleryRepository")->findAll()->where("lang = ?", $this->lang)->count();

        $this->template->results = $this->context->getService("galleryRepository")->findAll()->where("lang = ?", $this->lang)->limit($paginator->itemsPerPage, $paginator->offset);
        $this->template->galleryRepository = $this->context->getService("galleryRepository");
        $this->template->photosRepository = $this->context->getService("photoRepository");
    }

    public function actionEdit($id) {
        $this["galleryForm"]["type"]->setValue("edit");
        $this["galleryForm"]["id"]->setValue($id);

        $item = $this->context->getService("galleryRepository")->findById($id);
        $record = $item->fetch();

        if (!$record) {
            throw new BadRequestException;
        }


        $this['galleryForm']->setDefaults($record);
    }

    public function actionAdd() {
        $this["photoForm"]["type"]->setValue("insert");
    }

    // Form

    protected function createComponentGalleryForm()
    {
        $form = new GalleryForm();
        $form->onSuccess[] = [$this, "processGalleryForm"];
        return $form;
    }

    public function processGalleryForm(GalleryForm $form)
    {
        $values = $form->getValues();

        if($values->type == "edit") {

            // Unset redudant fields
            $id = $values->id;
            unset($values->id);
            unset($values->type);

            // Do query
            $result = $this->context->getService("galleryRepository")->update((array)$values, $id);

            if($result) {
                $this->flashMessage('Položka byla úspěšně upravena.');
            } else {
                $this->flashMessage('Položku se nepodařilo upravit, nebo nedošlo k žádné změně.');
            }

            $this->redirect("Gallery:default");

        } else {
            // Unset redudant fields
            unset($values->id);
            unset($values->type);

            // Do query
            $result = $this->context->getService("galleryRepository")->insert((array)$values + ["lang" => $this->lang]);

            if($result) {
                $this->flashMessage('Položka byla úspěšně přidána.');
                $this->redirect("Gallery:detail", $result);
            } else {
                $this->flashMessage('Položku se nepodařilo přidat.');
                $this->redirect("Gallery:default");
            }

        }
    }

    public function processUploadForm(PhotoForm $form)
    {
        $values = $form->getValues();

        if($values->type == "edit") {

            // Unset redudant fields
            $id = $values->id;
            unset($values->id);
            unset($values->type);
            unset($values->file);

            // Do query
            $result = $this->context->getService("photoRepository")->update((array)$values, $id);
            $photo = $this->context->getService("photoRepository")->findById($id)->fetch();

            if($result) {
                $this->flashMessage('Položka byla úspěšně upravena.');
            } else {
                $this->flashMessage('Položku se nepodařilo upravit, nebo nedošlo k žádné změně.');
            }

            $this->redirect("Gallery:detail", $photo->gallery_id);

        }
    }

    public function actionDeleteGallery($id) {
        $postRepository = $this->context->getService("galleryRepository");
        $postRepository->deleteById($id);
        $this->redirect("Gallery:default");
        exit;
    }

    public function actionDeletePhoto($id) {
        $postRepository = $this->context->getService("photoRepository");
        $photo = $this->context->getService("photoRepository")->findById($id)->fetch();
        $photoAlbum = $photo->gallery_id;
        $postRepository->deleteById($id);

        $this->redirect("Gallery:detail", $photoAlbum);
        exit;
    }

    // Gallery

    protected function createComponentPhotoForm()
    {
        $form = new PhotoForm();
        $form->onSuccess[] = [$this, "processUploadForm"];
        return $form;
    }

    public function actionDetail($id) {

        $session = $this->context->getService("session");
        $filter = $session->getSection("filter-user");
        if(!$filter->limit) {
            $filter->limit = 150;
        }
        $this->template->limit = $filter->limit;

        $vp = new VisualPaginator();
        $vp->loadState($this->request->getParameters());
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $filter->limit;
        $paginator->itemCount = $this->context->getService("photoRepository")->findAll()->where("gallery_id = ?", $id)->count();

        $this->template->results = $this->context->getService("photoRepository")->findAll()->where("gallery_id = ?", $id)->order("order DESC, id DESC")->limit($paginator->itemsPerPage, $paginator->offset);
        $this->template->gallery = $this->context->getService("galleryRepository")->findById($id)->fetch();
        $this->template->galleryRepository = $this->context->getService("galleryRepository");
        $this->template->photoRepository = $this->context->getService("photoRepository");

        $this->addComponent($vp, "vp");
    }

    public function actionPhotoDetail($id) {
        $this->template->result = $this->context->getService("photoRepository")->findById($id)->fetch();
    }

    public function actionEditPhoto($id) {
        $this["photoForm"]["type"]->setValue("edit");
        $this["photoForm"]["id"]->setValue($id);

        $item = $this->context->getService("photoRepository")->findById($id);
        $record = $item->fetch();

        if (!$record) {
            throw new BadRequestException("Page not found.");
        }


        $this['photoForm']->setDefaults($record);
    }

    public function actionUpload($galleryId) {

        $this->template->galleryId = $galleryId;

        $this["photoForm"]["type"]->setValue("insert");
        $this["photoForm"]["gallery_id"]->setValue($galleryId);
    }

    public function actionAjaxUpload() {
        $post = $this->request->getPost();

        foreach($post["files"] as $file) {
            $code_base64 = $file;
            $code_base64 = str_replace('data:image/jpeg;base64,','',$code_base64);
            $code_binary = base64_decode($code_base64);

            $upoadedFile = $this->context->getService("fileRepository")->storeFileFromString($code_binary);

            if($upoadedFile) {
                $this->context->getService("photoRepository")->insert(array(
                    "name" => $post["name"],
                    "is_public" =>  $post["is_public"],
                    "gallery_id" =>  $post["gallery_id"],
                    "file_id" => $upoadedFile->id
                ));
            }
        }

        echo 1; die;
    }

    public function actionReorderItems() {

        $rows = explode(";", $_POST["rows"]);

        foreach($rows as $key => $row) {
            if(!$row) {
                unset($rows[$key]);
            }
        }

        $max = count($rows) + 1;

        $i = 0;
        foreach($rows as $row) {
            $i++;
            $priority = ($max - $i) * 10;
            $rowRecord = $this->context->getService("photoRepository")->findById($row)->fetch();
            if($rowRecord) {
                $rowRecord->update(["order" => $priority]);
            }

        }

        echo 1; die;
    }

}