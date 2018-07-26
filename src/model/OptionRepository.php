<?php

namespace ContrastCms\Application;

class OptionRepository extends Repository
{
	public function change(array $data, $language = "cs_CZ")
	{

		foreach ($data as $prop => $val) {
			$row = $this->getTable()->where(array("key" => $prop, "lang" => $language))->fetch();
			if ($row) {
				$row->update(array("value" => $val));
			} else {
				$this->getTable()->insert([
					"key" => $prop, "lang" => $language, "value" => $val
				]);
			}

		}

		return true;
	}
}