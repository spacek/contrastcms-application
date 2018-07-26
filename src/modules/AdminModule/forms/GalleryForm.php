<?php

namespace ContrastCms\Application\AdminModule;

use Nette;
use Nette\Utils\Finder;

class GalleryForm extends Nette\Application\UI\Form
{

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// Storage type

		$this->addHidden('type', 'insert');
		$this->addHidden('id', 0);

		// First block
		$this->addText('name', 'Jméno:');
		$this->addText('priority', 'Priorita galerie:');
		$this->addTextArea('perex', 'Perex galerie:');

		$this->addSelect("is_public", "Veřejná galerie", array(
			1 => "Ano",
			2 => "Ne"
		));

		$this->addSubmit('save', 'Uložit změny')->getControlPrototype()->addAttributes(array("class" => "right"));
	}
}