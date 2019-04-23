<?php

namespace ContrastCms\Application\AdminModule;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;

final class UserForm extends Form
{

	public function __construct(IContainer $parent = NULL, $name = NULL, $roles = [])
	{
		parent::__construct($parent, $name);

		// Storage type

		$this->addHidden('type', 'insert');
		$this->addHidden('id', 0);

		// First block
		$this->addText('name', 'Jméno:');
		$this->addText('surname', 'Příjmení:');
		$this->addText('username', 'Uživatelské jméno:');
		$this->addPassword('password', 'Heslo:');
		$this->addText('email', 'E-mail:');
		$this->addSelect("group_id", "Typ uživatelského účtu", $roles);

		$this->addText('phone', 'Telefon:');
		$this->addText('skype', 'Skype:');

		$this->addSubmit('save', 'Uložit změny')->getControlPrototype()->addAttributes(array("class" => "right"));
	}
}