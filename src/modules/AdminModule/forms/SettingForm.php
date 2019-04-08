<?php

namespace ContrastCms\Application\AdminModule;

use Nette\ComponentModel\IContainer;

final class SettingForm extends \Nette\Application\UI\Form
{

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// Seo
		$this->addText('site_name', 'Název / titulek:');
		$this->addText('default_keywords', 'Klíčová slova:');
		$this->addText('default_description', 'Popisek:');

		// System
		$this->addText('version', 'Verze:');


		// Submit
		$this->addSubmit('save', 'Uložit změny')->getControlPrototype()->addAttributes(array("class" => "right"));
	}
}