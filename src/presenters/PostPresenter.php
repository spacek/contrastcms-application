<?php

class PostPresenter extends BasePresenter
{
	public function actionCategory($id)
	{
		$this->template->articles = $this->context->getService("postRepository")->findByParentAndLang($id, "cs_CZ")->where("is_public = ?", 1);
	}

	public function actionSitemapxml()
	{
		$this->template->posts = $this->context->getService("postRepository")->findAll()->where(array("lang" => $this->lang));
	}

	public function actionDetail($postId, $lang = null, $preview = 0)
	{
		$this->template->language = $this->lang;

		$post = $this->context->getService("postRepository");
		$data = $post->findByIdAndLang($postId, $lang)->fetch();


		if (!$data) {
			throw new \Nette\Application\BadRequestException("Stránka nenalezena");
		}

		if ($preview == 0) {

			if ($data->is_public == 0) {
				throw new \Nette\Application\BadRequestException("Stránka nenalezena");
			}
		}

		$aData = $data->toArray();

		$nData = new stdClass();

		foreach ($aData as $key => $val) {
			$nData->$key = $val;
		}

		$data = $nData;

		$this->template->preview = false;

		if ($data != null) {
			$this->template->post = $data;

			// Load attachments
			$this->template->attachments = $this->context->getService("postAttachmentRepository")->fetchByParentAndLang($postId, $lang, 0, 100)->order("priority DESC");
			$this->template->photos = $this->context->getService("postAttachmentRepository")->fetchByParentAndLang($postId, $lang, 0, 100)->where("type = ?", "image");
			$this->template->articles = $this->context->getService("postRepository")->findByParentAndLang($data->parent, $lang)->where("id <> ?", $postId)->limit(3);


			$fileAttachmentsCount = 0;
			$attachmentTypes = [];
			foreach ($this->template->attachments as $attachment) {
				if ($attachment['type'] == "file") {
					$fileAttachmentsCount++;
				}

				$attachmentTypes[] = $attachment["type"];
			}

			$this->template->areAttachments = ($fileAttachmentsCount > 0) ? true : false;

			// Load subitems (references / news)
			$this->template->subitems = $this->context->getService("postRepository")->findBy(array("parent" => $postId, "lang" => $lang, "is_public" => 1), "priority DESC, id DESC");

			// Next / prev
			$next = $this->context->getService("postRepository")->findAll()->where("id > ? AND parent = ?", array(
				$postId,
				$data->parent
			))->order("priority DESC, id ASC")->fetch();

			$prev = $this->context->getService("postRepository")->findAll()->where("id < ? AND parent = ?", array(
				$postId,
				$data->parent
			))->order("priority ASC, id DESC")->fetch();

			$this->template->nextUrl = ($next) ? $next->id : false;
			$this->template->prevUrl = ($prev) ? $prev->id : false;


			// Load submenu
			$mainItemId = $post->getMainItem($postId, $lang);

			// fetch tree for submenu
			$tree = \ContrastCms\Application\TreeProvider::getPartOfTree($post, $lang, $mainItemId, $postId, false);

			$this->template->submenu = $tree;
			$this->template->mainItem = $post->findByIdAndLang($mainItemId, $lang)->fetch();
		} else {
			throw new \Nette\Application\BadRequestException("Stránka nenalezena");
		}
	}
}