<?php

namespace ContrastCms\Application\AdminModule;
use Nette;
use Nette\Utils\Finder;

class PhotoForm extends Nette\Application\UI\Form
{

    public function __construct(IContainer $parent = NULL, $name = NULL)
    {
	    parent::__construct($parent, $name);

	    // Storage type

	    $this->addHidden('type', 'insert');
	    $this->addHidden('id', 0);
	    $this->addHidden('gallery_id', 0);

	    // First block
	    $this->addText('name', 'Titulek fotografie:');
        $this->addText('url', 'URL (jen pro některé slidery na HP):');
	    $this->addSelect("is_public", "Veřejná fotografie", array(
		    1 => "Ano",
		    2 => "Ne"
	    ));

	    $this->addUpload("file", "Vyberte soubor s fotogrfií");

	    $this->addSubmit('save', 'Uložit změny')->getControlPrototype()->addAttributes(array("class" => "right"));
    }
}