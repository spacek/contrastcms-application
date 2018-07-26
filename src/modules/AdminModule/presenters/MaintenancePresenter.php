<?php

namespace ContrastCms\Application\AdminModule;

use Nette\Utils\Strings;

final class MaintenancePresenter extends SecuredPresenter
{

	public function actionDuplicate($from, $to) {
		$to = explode(",", $to);

		$allPosts = $this->context->getService("postRepository")->findAll()->where("lang = ?", $from);
		foreach($allPosts as $post) {

			foreach($to as $lang) {
				$existingRecord = $this->context->getService("postRepository")->findByIdAndLang($post->id, $lang)->fetch();
				if(!$existingRecord) {
					$data = $post->toArray();
					$data['lang'] = $lang;
					$result = $this->context->getService("postRepository")->insert($data);

					if($result) {
						// duplicate attachments
						$attachments = $this->context->getService("postAttachmentRepository")->findBy(array("lang" => $from, "parent" => $post->id), "id ASC");

						foreach($attachments as $attachment) {
							/*$existingAttachmentRecord = $this->context->postAttachmentRepository->findBy(array("lang" => $lang, "parent" => $post->id), "id ASC")->fetch();

							if(!$existingAttachmentRecord) {
								$attachmentData = $attachment->toArray();
								$attachmentData['lang'] = $lang;
								unset($attachmentData['id']);
								$this->context->postAttachmentRepository->insert($attachmentData);
							}*/

							$attachmentData = $attachment->toArray();
							$attachmentData['lang'] = $lang;
							unset($attachmentData['id']);
							$this->context->getService("postAttachmentRepository")->insert($attachmentData);

						}
					}

				}
			}
		}
		echo "done"; exit;
	}

	public function actionDuplicateHomepage($from, $to) {
		$to = explode(",", $to);

		// duplicate attachments
		$attachments = $this->context->getService("postAttachmentRepository")->findBy(array("lang" => $from, "parent" => 0), "id ASC");

		foreach($attachments as $attachment) {			
			$attachmentData = $attachment->toArray();
			$attachmentData['lang'] = $to;
			unset($attachmentData['id']);
			$this->context->getService("postAttachmentRepository")->insert($attachmentData);

		}

		echo "done"; exit;
	}

	public function actionDuplicatePageAttachments($from, $to, $from_language = "cs_CZ", $to_language = "cs_CZ") {

		// duplicate attachments
		$attachments = $this->context->getService("postAttachmentRepository")->findBy(array("lang" => $from_language, "parent" => $from), "id ASC");

		foreach($attachments as $attachment) {
			$attachmentData = $attachment->toArray();
			$attachmentData['lang'] = $to_language;
			$attachmentData['parent'] = $to;
			unset($attachmentData['id']);
			$this->context->getService("postAttachmentRepository")->insert($attachmentData);

		}

		echo "done"; exit;
	}

	public function actionDuplicateCustomModuleRows($table, $from_language = "cs_CZ", $to_language = "cs_CZ") {
		// duplicate attachments
		$rows = $this->context->getService("crudRepository")->getTable($table)->where(array("lang" => $from_language))->order("id ASC");

		foreach($rows as $attachment) {
			$attachmentData = $attachment->toArray();
			$attachmentData['lang'] = $to_language;
			unset($attachmentData['id']);
			$this->context->getService("crudRepository")->getTable($table)->insert($attachmentData);

		}

		echo "done"; exit;
	}

	public function actionDuplicateGalleries($from, $to) {
		$to = explode(",", $to);

		$allPosts = $this->context->getService("galleryRepository")->findAll()->where("lang = ?", $from);
		foreach($allPosts as $post) {

			foreach($to as $lang) {
				$existingRecord = $this->context->getService("galleryRepository")->findAll()->where("id = ? AND lang = ?", [$post->id, $lang])->fetch();
				if(!$existingRecord) {
					$data = $post->toArray();
					$data['lang'] = $lang;
					unset($data["id"]);
					$result = $this->context->getService("galleryRepository")->insert($data);

					if($result) {
						// duplicate photos
						$photos = $this->context->getService("photoRepository")->findBy(array("gallery_id" => $post->id), "id ASC");

						foreach($photos as $attachment) {
							$attachmentData = $attachment->toArray();
							$attachmentData['gallery_id'] = $result->id;
							unset($attachmentData['id']);
							$this->context->getService("photoRepository")->insert($attachmentData);

						}
					}

				}
			}
		}
		echo "done"; exit;
	}

	public function actionFixSlugs() {

		$allPosts = $this->context->getService("crudRepository")->getTable("news");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->title)
			]);
		}

		$allPosts = $this->context->getService("crudRepository")->getTable("stories");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->title)
			]);
		}

		$allPosts = $this->context->getService("crudRepository")->getTable("tutors");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->name)
			]);
		}

		$allPosts = $this->context->getService("crudRepository")->getTable("team");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->name)
			]);
		}

		$allPosts = $this->context->getService("crudRepository")->getTable("references");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->name)
			]);
		}

		$allPosts = $this->context->getService("crudRepository")->getTable("blog");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->name)
			]);
		}

		$allPosts = $this->context->getService("crudRepository")->getTable("publications");
		foreach($allPosts as $post) {
			$post->update([
				"slug" => Strings::webalize($post->title)
			]);
		}

		echo "done"; die;
	}
}