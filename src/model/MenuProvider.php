<?php

namespace ContrastCms\Application;

class MenuProvider
{

	use \Nette\SmartObject;

	public static function getMenuObject($postRepository, $activeId = 0, $language = "cs_CZ")
	{
		$menuObject = array();
		$menuItems = $postRepository->findBy(array(
			'lang' => $language,
			'is_public' => 1,
			'in_menu' => 1,
			'parent' => 0
		), 'priority DESC, id ASC');

		foreach ($menuItems as $item) {

			$menuItem = new MenuItem($item->id, $item->id, $item->title, $item->lang, $item->menu_class);

			if ($item->id == $activeId) {
				$menuItem->setActive(true);
			}

			if (self::hasChildren($postRepository, $activeId, $language, $item->id)) {
				$children = (array)self::getChildren($postRepository, $activeId, $language, $item->id);
				$menuItem->addChildren($children);
			}

			$menuObject[] = $menuItem;
		}

		foreach ($menuObject as $menuItem) {
			if ($menuItem instanceof MenuItem && !$menuItem->isActive()) {
				if (self::findActiveDescendant($menuItem)) {
					$menuItem->setActive();
				}
			}
		}

		return $menuObject;
	}

	public static function findActiveDescendant(MenuItem $item)
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
	}

	public static function hasChildren($postRepository, $activeId = 0, $language, $postId)
	{

		$menuCount = $postRepository->countBy(array(
			'lang' => $language,
			'is_public' => 1,
			'parent' => $postId
		));

		if ($menuCount > 0) {
			return true;
		}

		return false;
	}

	public static function getChildren($postRepository, $activeId = 0, $language, $postId)
	{
		$menuObject = array();
		$subItems = $postRepository->findBy(array(
			'lang' => $language,
			'is_public' => 1,
			'parent' => $postId
		), 'priority DESC, id ASC');

		foreach ($subItems as $item) {
			$menuItem = new MenuItem($item->id, $item->id, $item->title, $item->lang, $item->menu_class);

			if ($item->id == $activeId) {
				$menuItem->setActive(true);
			}

			if (self::hasChildren($postRepository, $activeId, $language, $item->id)) {
				$children = (array)self::getChildren($postRepository, $activeId, $language, $item->id);
				$menuItem->addChildren($children);
			}

			$menuObject[] = $menuItem;
		}

		return $menuObject;
	}

	public static function getBreadcrumbsObject($postRepository, $activeId = 0, $language = "cs_CZ")
	{
		$menuObject = array();

		$item = $postRepository->findBy(array('id' => $activeId), 'priority DESC, id ASC')->fetch();

		if ($item) {
			$menuItem = new Crumb($item->id, $item->id, $item->title, $item->lang);
			$menuObject[] = $menuItem;
			if ($item->parent != 0) {
				$menuObject = self::addCrumb($postRepository, $menuObject, $item->parent);
			}
		}

		$menuObject = array_reverse($menuObject);

		return $menuObject;
	}

	public static function addCrumb($postRepository, $menu, $parent)
	{
		$item = $postRepository->findBy(array('id' => $parent), 'priority DESC, id ASC')->fetch();

		if ($item) {
			$menuItem = new Crumb($item->id, $item->id, $item->title, $item->lang);
			$menu[] = $menuItem;
			if ($item->parent != 0) {
				$menu = self::addCrumb($postRepository, $menu, $item->parent);
			}
		}

		return $menu;
	}

	public static function getBottomMenuObject($postRepository, $language = "cs_CZ")
	{
		$menuObject = array();
		$menuItems = $postRepository->findBy(array(
			'lang' => $language,
			'is_public' => 1,
			'in_bottom_menu' => 1
		), 'priority DESC, id ASC');

		foreach ($menuItems as $item) {

			$menuItem = new MenuItem($item->id, $item->id, $item->title, $item->lang, $item->menu_class);

			$menuObject[] = $menuItem;
		}

		return $menuObject;
	}
}