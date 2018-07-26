<?php


/**
 * BasicMenu Control
 */

namespace ContrastCms\Application\AdminModule;
use Nette;

final class LeftTree extends Nette\Application\UI\Control
{
	private $menuItems;

	public function setData(array $menuItems)
	{
		$this->menuItems = $menuItems;
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/LeftTree.latte');
        $template->menu = $this->menuItems;
        $template->language = "cs_CZ";
		$template->render();
	}
}