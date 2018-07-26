<?php

namespace ContrastCms\Application;

use Nette\SmartObject;

class TreeProvider
{

	use SmartObject;

	/**
	 * Returns menu object
	 * (can be also used for preparing tree or sitemap with small modifications)
	 * @param $postRepository
	 * @param string $language
	 * @return array
	 */
	public static function getTreeObject($postRepository, $language = "cs_CZ", $bMap = false, $activeId = 0)
	{
		$menuObject = array();
		$menuItems = $postRepository->findBy(array(
			'lang' => $language,
			'parent' => 0
		), 'priority DESC, version DESC');

		foreach ($menuItems as $item) {

			$menuItem = new TreeItem($item->id, $item->id, $item->title, $item->is_public, $item->lang, $item->is_unfolded);

			if ($item->id == $activeId) {
				$menuItem->setActive(true);
			}

			if (self::hasChildren($postRepository, $language, $item->id)) {
				$children = (array)self::getChildren($postRepository, $language, $item->id);
				$menuItem->addChildren($children);
			}


			$menuObject[] = $menuItem;
		}

		// Check / set menu activity for parents etc.

		foreach ($menuObject as $menuItem) {
			if ($menuItem instanceof MenuItem && !$menuItem->isActive()) {
				if (self::findActiveDescendant($menuItem)) {
					$menuItem->setActive();
				}
			}
		}

		return $menuObject;
	}

	public static function findActiveDescendant(TreeItem $item)
	{
		$descendants = $item->subItems;
		foreach ($descendants as $descendant) {
			if ($descendant->isActive()) {
				return true;
			} else {
				if (self::findActiveDescendant($descendant)) {
					$descendant->setActive();
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns bool value whether item is containing children.
	 * @param $postRepository
	 * @param $language
	 * @param $postId
	 * @return bool
	 */
	public static function hasChildren($postRepository, $language, $postId)
	{
		$menuCount = $postRepository->countBy(array(
			'lang' => $language,
			'parent' => $postId
		));

		if ($menuCount > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Returns array filled with MenuItem objects
	 * @param $postRepository
	 * @param $language
	 * @param $postId
	 * @return bool
	 */
	public static function getChildren($postRepository, $language, $postId, $activeId = 0, $getUnpublished = true)
	{
		$menuObject = array();

		$requirements = array(
			'lang' => $language,
			'parent' => $postId,
		);

		if (!$getUnpublished) {
			$requirements['is_public'] = 1;
		}

		$subItems = $postRepository->findBy($requirements, 'priority DESC, version DESC');

		foreach ($subItems as $item) {

			$menuItem = new TreeItem($item->id, $item->id, $item->title, $item->is_public, $item->lang, $item->is_unfolded);

			if ((int)$item->id == (int)$activeId) {
				$menuItem->setActive(true);
			} else {
				$menuItem->setActive(false);
			}


			if (self::hasChildren($postRepository, $language, $item->id)) {
				$children = (array)self::getChildren($postRepository, $language, $item->id, $activeId, $getUnpublished);
				$menuItem->addChildren($children);
			}

			$menuObject[] = $menuItem;
		}

		return $menuObject;
	}

	public static function getPartOfTree($postRepository, $language, $postId, $activeId = 0, $displayUnpublished = true)
	{

		$menuObject = array();
		$requirements = array(
			'lang' => $language,
			'parent' => $postId,
		);

		if (!$displayUnpublished) {
			$requirements['is_public'] = 1;
		}

		$subItems = $postRepository->findBy($requirements, 'priority DESC, version DESC');

		foreach ($subItems as $item) {

			$menuItem = new TreeItem($item->id, $item->id, $item->title, $item->is_public, $item->lang, $item->is_unfolded);

			if ((int)$item->id == (int)$activeId) {
				$menuItem->setActive(true);
			} else {
				$menuItem->setActive(false);
			}


			if (self::hasChildren($postRepository, $language, $item->id)) {
				$children = (array)self::getChildren($postRepository, $language, $item->id, $activeId, $displayUnpublished);
				$menuItem->addChildren($children);
			}

			$menuObject[] = $menuItem;
		}

		// Check / set menu activity for parents etc.
		foreach ($menuObject as $menuItem) {
			if ($menuItem instanceof TreeItem && !$menuItem->isActive()) {
				if (self::findActiveDescendant($menuItem)) {
					$menuItem->setActive(true);
				}
			}
		}

		\Nette\Diagnostics\Debugger::$maxDepth = 5;

		//dump($menuObject);

		return $menuObject;
	}

}