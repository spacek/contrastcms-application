<?php

namespace ContrastCms\Application\AdminModule;
use Nette;

class AttachmentForm extends Nette\Application\UI\Form
{

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// Storage type

		$this->addHidden('operation_type', 'insert');
		$this->addHidden('parent', 0);
		$this->addHidden('lang', 'cs_CZ');
		$this->addHidden('id', 0);

		$priorities = array();
		for ($i = 0; $i <= 100; $i++) {
			$val = $i * 10;
			$priorities[$val] = $val;
		}

		// First block
		$this->addSelect("type", "Typ komponeny", array());

		$this->addUpload('file', 'Soubor nebo obrázek 1:')->getControlPrototype()->addAttributes(array("class" => "upload"));
		$this->addUpload('file2', 'Soubor nebo obrázek 2:')->getControlPrototype()->addAttributes(array("class" => "upload"));
		$this->addUpload('file3', 'Soubor nebo obrázek 3:')->getControlPrototype()->addAttributes(array("class" => "upload"));
		$this->addTextArea('title', 'Název / titulek:')->addRule($this::MAX_LENGTH, "Maximální délka textu je %d znaků.", 255)->setRequired(false);
		$this->addText('name', 'Systémové označení / poznámka:')->addRule($this::MAX_LENGTH, "Maximální délka textu je %d znaků.", 255)->setRequired(false);
		$this->addText('url', 'URL:')->addCondition($this::FILLED)->addRule($this::URL, "Zadejte platné URL")->setRequired(false);
		$this->addText('param1', 'Volitelný parametr 1:')->setRequired(false);
		$this->addSelect('priority', 'Priorita:', $priorities);
		$this->addSelect('gallery', 'Galerie (u slideshow):', array());

		// Text
		$this->addTextArea("text", "Textový box 1")->getControlPrototype()->addAttributes(array("class" => "ckeditor"));
		$this->addTextArea("text2", "Textový box 2")->getControlPrototype()->addAttributes(array("class" => "ckeditor"));
		$this->addTextArea("text3", "Textový box 3")->getControlPrototype()->addAttributes(array("class" => "ckeditor"));

		// Submit
		$this->addSubmit('save', 'Uložit změny')->getControlPrototype()->addAttributes(array("class" => "right"));
	}
}