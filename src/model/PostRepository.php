<?php

namespace ContrastCms\Application;

use Nette\Application\UI\Presenter;

class PostRepository extends Repository
{
	public function search($query, $language = "cs_CZ", $limit = 100, Presenter $presenter)
	{

		$conditions = "(title LIKE ? OR keywords LIKE ? OR description LIKE ? OR content LIKE ? OR title LIKE ? OR keywords LIKE ? OR description LIKE ? OR content LIKE ?) AND lang = ? AND is_public = 1";
		$params = array();
		for ($i = 1; $i < 5; $i++) {
			$params[] = "%" . $query . "%";
			$params[] = "%" . htmlentities($query) . "%";
		}

		$params[] = $language;

		$results = $this->getTable()->where($conditions, $params)->group("id");

		$aPapers = array();


		foreach ($results as $row) {
			$limit--;
			if ($limit == 0) {
				return $aPapers;
			}
			$aPapers["post_" . $row->id] = $this->createSearchItem($row->id, $row->title, "Post:detail", [$row->id, $language], $presenter, $language);
		};

		// Search by attachments

		$conditions = "(title LIKE ? OR title2 LIKE ? OR title3 LIKE ? OR text LIKE ? OR text2 LIKE ? OR text3 LIKE ? OR title LIKE ? OR title2 LIKE ? OR title3 LIKE ? OR text LIKE ? OR text2 LIKE ? OR text3 LIKE ?) AND lang = ?";
		$params = array();
		for ($i = 1; $i < 7; $i++) {
			$params[] = "%" . $query . "%";
			$params[] = "%" . htmlentities($query) . "%";
		}

		$params[] = $language;

		$results = $this->connection->table("post_attachment")->where($conditions, $params);

		foreach ($results as $row) {
			$_row = $this->find()->where("id = ? AND is_public = 1 AND lang = ?", [$row->parent, $language])->group("id")->fetch();
			if ($_row) {
				$aPapers["post_" . $_row->id] = $this->createSearchItem($_row->id, $_row->title, "Post:detail", [$_row->id, $language], $presenter, $language);
			}

			$limit--;
			if ($limit == 0) {
				return $aPapers;
			}
		};

		foreach ($presenter->customModules as $module => $data) {
			$url = $data[0];
			unset($data[0]);

			$params = [];
			$conditions = [];


			$results = $this->connection->table($module)->where("lang = ?", $language);

			$i = 0;
			foreach ($data as $col) {
				$i++;
				if ($i == 1) {
					$firstItemData = $col;
				}
				$conditions[] = $col;
				$conditions[] = $col;
				$params[] = "%" . $query . "%";
				$params[] = "%" . htmlentities($query) . "%";
			}

			$results->where(implode(" LIKE ? OR ", $conditions) . " LIKE ?", $params);

			foreach ($results as $row) {
				$aPapers[$module . "_" . $row->id] = $this->createSearchItem($row->id, $row->{$firstItemData}, $url, [$row->id], $presenter, $language);
				$limit--;
				if ($limit == 0) {
					return $aPapers;
				}
			};
		}


		return $aPapers;
	}

	public function createSearchItem($id, $name, $url, $urlParams, $presenter, $language)
	{

		$std = new \stdClass();
		$std->title = $name;
		$std->id = $id;
		$std->url = $presenter->link($url, $urlParams + ["lang" => $language]);

		return $std;
	}

	public function getMainItem($id, $lang = "cs_CZ")
	{

		$parent = $id;
		$preParent = $id;

		while ($parent > 0) {
			$preParent = $parent;
			$parent = $this->findIdParentIncludingLanguage($parent, $lang);
		}

		return $preParent;
	}

	public function countByParent($id)
	{
		return $this->countBy(array("parent" => $id));
	}

	public function findIdParent($nIdChild)
	{
		if ($nIdChild == 0) {
			return 0;
		}

		$row = $this->findById($nIdChild);
		$row = $row->fetch();

		return (is_null($row)) ?
			0 : (int)$row->parent;
	}

	public function findIdParentIncludingLanguage($nIdChild, $lang = "cs_CZ")
	{
		if ($nIdChild == 0) {
			return 0;
		}

		$row = $this->findByIdAndLang($nIdChild, $lang, false);
		$row = $row->fetch();

		return (is_null($row)) ?
			0 : (int)$row->parent;
	}

	public function getFirstChildId($nId, $lang = "cs_CZ")
	{
		$menuItems = $this->findBy(array(
			'lang' => $lang,
			'parent' => $nId
		), 'priority DESC, version DESC');

		$item = $menuItems->fetch();

		if ($item) {
			return $item->id;
		}

		return 0;
	}

	public function getFirstChild($nId, $lang = "cs_CZ")
	{
		$menuItems = $this->findBy(array(
			'lang' => $lang,
			'parent' => $nId,
			'is_public' => 1
		), 'priority DESC, version DESC');

		$item = $menuItems->fetch();

		if ($item) {
			return $item;
		}

		return false;
	}

	public function findChilds($nIdParent)
	{
		return $this->getTable()->where(array("parent" => (int)$nIdParent, "is_public" => 1));
	}

	public function getChildrenIds($id)
	{

		$items = $this->findPublicByParent($id);
		$ids = array();
		foreach ($items as $item) {
			$ids[] = $item->id;
		}

		return $ids;
	}


	public function fetchChilds($nIdParent)
	{
		$aChilds = array();
		$result = $this->getTable()->where(array("parent" => (int)$nIdParent, "is_public" => 1, "lang" => "cs_CZ"));

		foreach ($result as $row) {
			$aChilds[] = $row;
		};

		return $aChilds;
	}

	public function hasChildren($nIdParent)
	{
		$count = $this->getTable()->where(array("parent" => (int)$nIdParent, "is_public" => 1))->count();
		return $count ? true : false;
	}

	public function findChildsAndSort($nIdParent, $lang = "cs_CZ", $sort = "id DESC", $limit = 100)
	{
		$aChilds = array();
		$result = $this->getTable()->where(array("parent" => (int)$nIdParent, "lang" => $lang, "is_public" => 1))->order($sort)->limit($limit);

		foreach ($result as $row) {
			$aChilds[] = $row;
		};

		return $aChilds;
	}

	public function findChildsByMonthsAndSort($nIdParent, $lang = "cs_CZ", $month = "", $sort = "id DESC", $limit = 100)
	{

		$aChilds = array();
		$result = $this->getTable()->where("(created_at >= ? AND created_at <= ?) AND parent = ? AND lang = ? AND is_public = 1", $month . "-01", $month . "-31", $nIdParent, $lang)->order($sort)->limit($limit);

		foreach ($result as $row) {
			$aChilds[] = $row;
		}

		return $aChilds;
	}

	public function getSelectableChildren($nIdParent, $nDepth = 0, $lang = "cs_CZ")
	{
		$aChilds = array();
		$result = $this->getTable()->where(array("parent" => (int)$nIdParent, "lang" => $lang));

		foreach ($result as $row) {
			$aChilds[$row->id] = $row->title;

			if ($nDepth >= 1) {
				$resultParent = $this->getTable()->where(array("parent" => (int)$row->id, "lang" => $lang));
				foreach ($resultParent as $rowParent) {
					$aChilds[$rowParent->id] = "-- " . $rowParent->title;
				}
			}
		}

		return $aChilds;
	}

	public function getTitle($id, $lang)
	{
		$post = $this->findByIdAndLang($id, $lang);
		if ($post) {

			$postData = $post->fetch();
			if ($postData) {
				return $postData->title;
			}
		}

		return "";
	}

	public function findByIdAndLang($id, $lang, $publicOnly = true)
	{
		$selection = $this->getTable()->where(array(
			"id" => (int)$id,
			"lang" => $lang,
		));

		if ($publicOnly) {
			$selection->where("is_public = ?", 1);
		}

		return $selection;
	}

	public function findByParentAndLang($id, $lang)
	{
		return $this->getTable()->where(array(
			"parent" => (int)$id,
			"lang" => $lang,
			"is_public" => 1
		));
	}

	public function findByParent($id)
	{
		return $this->getTable()->where(array(
			"parent" => (int)$id
		));
	}

	public function findPublicByParent($id)
	{
		return $this->getTable()->where(array(
			"parent" => (int)$id,
			"is_public" => 1,
			"lang" => "cs_CZ"
		));
	}

	public function updateByIdAndLang(array $data, $id, $lang)
	{
		return $this->getTable()->where(array("id" => (int)$id, 'lang' => $lang))->update($data);
	}

	public function idToSlug($val, $lang = "cs_CZ")
	{

		$row = $this->findByIdAndLang($val, $lang, false)->fetch();
		return $row->slug;
	}

	public function slugToId($val, $lang = "cs_CZ")
	{

		$row = $this->find()->where("slug = ?", $val)->where("lang = ?", $lang)->fetch();

		if ($row) {
			return $row->id;
		}

		$array = explode("-", $val);
		return end($array);
	}
}