<?php

namespace ContrastCms\Application;

use Nette\SmartObject;

class Crumb {

	use SmartObject;

    public $postId;
    public $postUrl;
    public $title;
    public $lang;
    public $active;
    public $subItems = array();

    public function __construct($postId, $postUrl, $title, $lang) {
        $this->postId = $postId;
        $this->title = $title;
        $this->lang = $lang;
        $this->postUrl = $postUrl;
        $this->active = false;

    }
}