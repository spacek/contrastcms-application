<?php

namespace ContrastCms\Application\AdminModule;

use ContrastCms\VisualPaginator\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Utils\Strings;

class PostPresenter extends SecuredPresenter
{

	public $attachmentTypes = array(
		"text" => "Textový box",
		"image" => "Obrázek",
	);

	public function startup()
	{
		parent::startup();
		$this->template->attachmentTypes = $this->attachmentTypes;
	}

	public function actionEdit($id, $lang = "cs_CZ")
	{

		$this["postForm"]["type"]->setValue("edit");
		$this["postForm"]["id"]->setValue($id);

		$item = $this->context->getService("postRepository")->findByIdAndLang($id, $lang, false);
		$record = $item->fetch();

		if (!$record) {

			// try to find by ID
			$item = $this->context->getService("postRepository")->findById($id)->order("lang = 'cs_CZ' DESC")->fetch();
			if ($item) {
				$values = $item->toArray();
				$values["lang"] = $lang;
				$this->context->getService("postRepository")->insert($values);
			}

			$item = $this->context->getService("postRepository")->findByIdAndLang($id, $lang);
			$record = $item->fetch();
			if (!$record) {
				throw new BadRequestException;
			}
		}

		// Populate
		$this['postForm']->setDefaults($record);

		$parent = $this->context->getService("postRepository")->findById($record->parent)->fetch();

		$this->template->id = $id;
		$this->template->parent = $record->parent;
		$this->template->parentParent = $record->parent;
		$this->template->image = $record->file_id;
		if ($parent) {
			$this->template->parentParent = $parent->parent;
		}
		$this->template->lang = $record->lang;

		$this->template->values = $record;
	}

	public function actionDuplicate($from, $language = "cs_CZ")
	{

		// Crete new page

		$item = $this->context->getService("postRepository")->findByIdAndLang($from, $language, false);
		$record = $item->fetch();
		$data = (array)$record->toArray();
		unset($data["id"]);
		$data["title"] = "Copy: " . $record["title"];
		$newItem = $this->context->getService("postRepository")->insert($data);

		// duplicate attachments
		$attachments = $this->context->getService("postAttachmentRepository")->findBy(array("lang" => $language, "parent" => $from), "id ASC");

		foreach ($attachments as $attachment) {
			$attachmentData = $attachment->toArray();
			$attachmentData['lang'] = $language;
			$attachmentData['parent'] = $newItem->id;
			unset($attachmentData['id']);
			$this->context->getService("postAttachmentRepository")->insert($attachmentData);

		}

		$this->flashMessage("Úspěšně zkopírováno");
		$this->redirect("Homepage:default");
	}

	public function actionAddAttachment($id, $lang)
	{
		$this["attachmentForm"]["operation_type"]->setValue("insert");
		$this["attachmentForm"]["parent"]->setValue($id);
		$this["attachmentForm"]["lang"]->setValue($lang);
	}


	public function actionMoveItem()
	{
		$json = $_POST["json"];
		$data = json_decode($json);
		$data = $data[0];
		$this->_changeStrucutre($data);
		echo 1;
		exit;
	}

	protected function createComponentAttachmentForm()
	{
		$form = new AttachmentForm();
		$form->onSuccess[] = [$this, "processAttachmentForm"];
		$form["type"]->setItems($this->attachmentTypes);
		$form["gallery"]->setItems(array($this->context->getService("galleryRepository")->getSelectableItems()));
		return $form;
	}

	public function _changeStrucutre($data)
	{
		$post = $this->context->getService("postRepository");
		if (isset($data->id) && isset($data->children)) {
			$weight = count($data->children) + 1;
			foreach ($data->children as $child) {
				$weight--;
				$post->update(array("parent" => $data->id, "priority" => $weight), $child->id);

				$this->_changeStrucutre($child);
			}
		}
	}

	public function actionSaveCollapsion()
	{
		if (isset($_POST['id']) && isset($_POST['value'])) {
			$this->context->getService("postRepository")->update(array("is_unfolded" => (int)$_POST['value']), (int)$_POST['id']);
		}

		echo 1;
		exit;
	}


	public function actionAttachments($id, $lang)
	{


		$this["postForm"]["type"]->setValue("edit");
		$this["postForm"]["id"]->setValue($id);

		$this->template->id = $id;
		$this->template->lang = $lang;
		$this->template->posts = $this->context->getService("postRepository");

		// List
		$session = $this->context->getService("session");
		$filter = $session->getSection("filter-attachments-" . $id);
		$filter->limit = 500;
		$this->template->limit = $filter->limit;

		$vp = new VisualPaginator();
		$vp->loadState($this->request->getParameters());
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $filter->limit;
		$paginator->itemCount = $this->context->getService("postAttachmentRepository")->countByParent($id);

		$this->template->results = $this->context->getService("postAttachmentRepository")->fetchByParentAndLang($id, $lang, $paginator->offset, $paginator->itemsPerPage)->order("priority DESC");

		$this->addComponent($vp, "vp");

	}

	public function actionReorderAttachments()
	{
		$rows = explode(";", $_POST["rows"]);

		foreach ($rows as $key => $row) {
			if (!$row) {
				unset($rows[$key]);
			}
		}

		$max = count($rows) + 1;

		$i = 0;
		foreach ($rows as $row) {
			$i++;
			$priority = ($max - $i) * 10;
			$rowRecord = $this->context->getService("postAttachmentRepository")->findById($row)->fetch();
			$rowRecord->update(["priority" => $priority]);
		}

		echo 1;
		die;
	}

	public function actionEditAttachment($id)
	{
		$this["attachmentForm"]["operation_type"]->setValue("edit");

		// Load other data

		$item = $this->context->getService("postAttachmentRepository")->findById($id);
		$record = $item->fetch();

		if (!$record) {
			throw new BadRequestException;
		}

		$this->template->id = $record->parent;
		$this->template->lang = $record->lang;

		// Populate
		$this['attachmentForm']->setDefaults($record);
	}

	public function actionAdd($id)
	{
		$this->template->id = $id;
		$this->template->parent = $id;

		$parent = $this->context->getService("postRepository")->findById($id)->fetch();

		$this->template->parentParent = $id;
		if ($parent) {
			$this->template->parentParent = $parent->parent;
		}

		$this->template->lang = "cs_CZ";
		$this["postForm"]["type"]->setValue("insert");
		$this["postForm"]["id"]->setValue($id);
	}

	public function actionCopy($id, $lang)
	{
		$item = $this->context->getService("postRepository")->findByIdAndLang($id, $lang, false);
		$record = $item->fetch();
		$data = (array)$record->toArray();
		unset($data["id"]);
		$this->context->getService("postRepository")->insert($data);
		$this->flashMessage("Úspěšně zkopírováno");
		$this->redirect("Homepage:default");
	}


	public function actionDelete($id, $lang = "cs_CZ")
	{
		$postRepository = $this->context->getService("postRepository");
		$postRepository->deleteById($id);

		$this->flashMessage("Úspěšně odstraněno.");
		$this->redirect("Homepage:default", array('lang' => $lang));
	}

	public function actionDeleteFile($fileId, $fieldToNull, $parentId)
	{

		$item = $this->context->getService("postRepository")->findById($parentId)->fetch();
		$item->update(array($fieldToNull => null));
		$this->context->getService("fileRepository")->deleteById($fileId);

		$this->flashMessage("Úspěšně odstraněno.");
		$this->redirect("Post:edit", $parentId);
	}

	public function actionDeleteAttachment($id)
	{

		$postRepository = $this->context->getService("postAttachmentRepository");
		$postRepository->deleteById($id);

		$this->redirectUrl($_SERVER['HTTP_REFERER']);
		exit;
	}

	public function actionPublish($id, $lang = "cs_CZ")
	{
		$postRepository = $this->context->getService("postRepository");
		$postRepository->updateByIdAndLang(array(
			'is_public' => 1
		), $id, $lang);

		$this->redirectUrl($_SERVER['HTTP_REFERER']);
		exit;
	}

	public function actionUnpublish($id, $lang = "cs_CZ")
	{
		$postRepository = $this->context->getService("postRepository");
		$postRepository->updateByIdAndLang(array(
			'is_public' => 0
		), $id, $lang);

		$this->redirectUrl($_SERVER['HTTP_REFERER']);
		exit;

	}

	// Form

	protected function createComponentPostForm()
	{
		$form = new PostForm();
		$form->onSuccess[] = [$this, "processPostForm"];
		return $form;
	}

	private function storeDataIntoSession($values)
	{
		$session = $this->context->getService("session");

		foreach ($values as $key => $val) {
			$sec = $session->getSection("preview_{$key}");
			$sec->data = $val;
		}

		$this->redirect(":Post:detail", array("postId" => $values->id, "preview" => 1));
		exit;
	}

	public function processPostForm(PostForm $form)
	{
		$values = $form->getValues();

		$lang = $values->lang;

		$values->slug = Strings::webalize($values->title);

		if ((int)$values->is_preview == 1) {
			$this->storeDataIntoSession($values);
			exit;
		}

		unset($values->is_preview);

		if ($values->type == "edit") {

			// Unset redudant fields
			$id = $values->id;
			$lang = $values->lang;
			unset($values->id);
			unset($values->type);

			// Store file
			if ($values->file->isOk()) {

				if ($values->file->isImage()) {
					$fileType = "image";
				} else {
					$fileType = "file";
				}

				$file_id = $this->context->getService("fileRepository")->storeFile($values->file, $fileType);

				$values->file_id = $file_id;
			}
			unset($values->file);


			// Do query

			$result = $this->context->getService("postRepository")->updateByIdAndLang((array)$values, $id, $lang);

			if ($result) {
				$this->flashMessage('Položka byla úspěšně upravena.');
			} else {
				$this->flashMessage('Položku se nepodařilo upravit, nebo nedošlo k žádné změně.');
			}

			$this->redirect("Post:edit", $id, $lang);

		} else {
			// Unset redudant fields
			$id = $values->id;
			unset($values->id);
			unset($values->type);

			// Extend store array
			$values->type = "post";
			$values->parent = $id;
			$values->is_removable = 1;
			$values->version = 1;
			$values->updated_at = date("Y-m-d H:i:s");
			$values->priority = 1;
			$values->owner = 1;

			if ($values->parent > 0) {
				$values->in_menu = 0;
				$values->in_bottom_menu = 0;
			}

			// Store file
			if ($values->file->isOk()) {

				if ($values->file->isImage()) {
					$fileType = "image";
				} else {
					$fileType = "file";
				}

				if ($fileType == "image") {
					$file_id = $this->context->getService("fileRepository")->storeImage($values->file, $fileType, true);
				} else {
					$file_id = $this->context->getService("fileRepository")->storeFile($values->file, $fileType);
				}

				$values->file_id = $file_id->id;
			}
			unset($values->file);


			$languages = $this->enabledLanguages;
			if (($key = array_search($values->lang, $languages)) !== false) {
				unset($languages[$key]);
			}

			// Do query
			$result = $this->context->getService("postRepository")->insert((array)$values);
			$lastRow = $this->context->getService("postRepository")->find()->order("id DESC")->fetch();

			foreach ($languages as $_lang) {
				$valuesTemporary = $values;
				$valuesTemporary->id = $lastRow->id;
				$valuesTemporary->lang = $_lang;
				$this->context->getService("postRepository")->insert((array)$valuesTemporary);
			}

			if ($result) {
				$this->flashMessage('Položka byla úspěšně přidána.');
				$this->redirect("Post:edit", $lastRow->id, $lang);
			} else {
				$this->flashMessage('Položku se nepodařilo přidat.');
				$this->redirect("Post:add");
			}


		}
	}

	public function processAttachmentForm(AttachmentForm $form)
	{
		$values = $form->getValues();

		// Store file
		if ($values->file->isOk()) {

			if ($values->file->isImage()) {
				$fileType = "image";
			} else {
				$fileType = "file";
			}

			$file_id = $this->context->getService("fileRepository")->storeFile($values->file, $fileType);
			$values->file_id = $file_id;
		}
		unset($values->file);

		// Store file 2
		if ($values->file2->isOk()) {

			$fileType = "file";
			if ($values->file2->isImage()) {
				$fileType = "image";
			}

			$file2_id = $this->context->getService("fileRepository")->storeFile($values->file2, $fileType);
			$values->file2_id = $file2_id;
		}
		unset($values->file2);

		// Store file 3
		if ($values->file3->isOk()) {

			$fileType = "file";
			if ($values->file3->isImage()) {
				$fileType = "image";
			}

			$file3_id = $this->context->getService("fileRepository")->storeFile($values->file3, $fileType);
			$values->file3_id = $file3_id;
		}
		unset($values->file3);

		if ($values->operation_type == "edit") {

			// Unset redudant fields
			$id = $values->id;
			unset($values->id);
			unset($values->operation_type);

			// Do query
			$result = $this->context->getService("postAttachmentRepository")->update((array)$values, $id);

			if ($result) {
				$this->flashMessage('Položka byla úspěšně upravena.');
			} else {
				$this->flashMessage('Položku se nepodařilo upravit, nebo nedošlo k žádné změně.');
			}

			$this->redirect("Post:attachments", $values->parent, $values->lang);

		} else {
			// Unset redudant fields
			unset($values->id);
			unset($values->operation_type);


			// Extend store array

			$result = $this->context->getService("postAttachmentRepository")->insert((array)$values);

			if ($result) {
				$this->flashMessage('Položka byla úspěšně přidána.');
			} else {
				$this->flashMessage('Položku se nepodařilo přidat.');
			}

			$this->redirect("Post:attachments", $values->parent, $values->lang);

		}
	}
}