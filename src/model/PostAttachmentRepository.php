<?php

namespace ContrastCms\Application;

class PostAttachmentRepository extends Repository
{
	public function countByParent($id)
	{
		return $this->getTable()->where("parent = ?", $id)->count();
	}

	public function fetchByParent($id, $offset, $limit)
	{
		return $this->getTable()->where("parent = ?", $id)->limit($limit, $offset);
	}

	public function getImage($id)
	{
		$row = $this->getTable()->where("parent = ? AND type = ?", $id, "image")->limit(1)->fetch();

		if ($row) {
			return $row->file_id;
		}
	}

	public function getImages($id)
	{
		$rows = $this->getTable()->where("parent = ? AND type = ?", $id, "image");
		return $rows;
	}

	public function fetchByParentAndLang($id, $lang, $offset, $limit)
	{
		return $this->getTable()->where("parent = ? AND lang = ?", $id, $lang)->limit($limit, $offset);
	}


}