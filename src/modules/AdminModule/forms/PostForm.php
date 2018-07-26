<?php

namespace ContrastCms\Application\AdminModule;

use Nette;
use Nette\Utils\Finder;
use Nette\Forms\Container;
use Nette\Forms;

final class PostForm extends Nette\Application\UI\Form
{

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// Storage type
		$this->addHidden('type', 'insert');
		$this->addHidden('id', 0);
		$this->addHidden('lang', 'cs_CZ');
		$this->addHidden('template', 'clanek');

		// First block
		$this->addText('keywords', 'Klíčová slova:');
		$this->addText('description', 'VOLITELNÝ POPIS STRÁNKY (pokud není vyplněn použije se NÁZEV DOKUMENTU)');
		$this->addText('title', 'NÁZEV DOKUMENTU V MENU:');
		$this->addHidden('is_public', 1);
		$this->addText('created_at', 'Datum:', 10)->setValue(date("Y-m-d"));
		$this->addHidden('are_comments_allowed', 0);
		$this->addHidden('in_menu', 1);
		$this->addSelect('in_bottom_menu', 'Zobrazovat odkaz v patiččce webu', [0 => "NE", 1 => "ANO"]);
		$this->addHidden("is_preview", 0);

		// Second block
		$this->addTextArea('name', 'Název dokumentu:');
		$this->addUpload('file', 'Zástupný obrázek:')->getControlPrototype()->addAttributes(array("class" => "upload"));
		$this->addTextArea('perex', 'PEREX DOKUMENTU (ÚVODNÍ TEXT):')->getControlPrototype()->addAttributes(array("class" => "ckeditor"));
		$this->addTextArea('content', 'TEXT DOKUMENTU:')->getControlPrototype()->addAttributes(array("class" => "ckeditor"));


		// Submit
		$this->addSubmit('preview', 'Náhled stránky')->getControlPrototype()
			->addAttributes(array("class" => "left",
				"onclick" => "return displayPreview()"));

		$this->addSubmit('save', 'Uložit změny')->getControlPrototype()
			->addAttributes(array("class" => "right",
				"onclick" => "return ulozit()"));
	}
}