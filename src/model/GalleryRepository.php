<?php

namespace ContrastCms\Application;

class GalleryRepository extends Repository
{
	public function getSelectableItems()
	{
		$all = $this->findAll();
		$selectable = array(0 => "- nic -");
		foreach ($all as $item) {
			$selectable[$item->id] = $item->name;
		}
		return $selectable;
	}
}