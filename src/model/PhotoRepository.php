<?php

namespace ContrastCms\Application;

class PhotoRepository extends Repository
{
	public function getFirstImage($galleryId)
	{
		return $this->findBy(array("gallery_id" => $galleryId), "id ASC")->fetch();
	}

	public function getImages($galleryId)
	{
		return $this->findBy(array("gallery_id" => $galleryId), "order DESC, id DESC");
	}

	public function getLatestImages()
	{
		return $this->findBy(array(), "id DESC");
	}
}