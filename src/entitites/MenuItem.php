<?php

namespace ContrastCms\Application;

class MenuItem {

	use \Nette\SmartObject;

    public $postId;
    public $postUrl;
    public $title;
    public $lang;
    public $active;
    public $subItems = array();

    public function __construct($postId, $postUrl, $title, $lang, $menu_class) {
        $this->postId = $postId;
        $this->title = $title;
        $this->lang = $lang;
        $this->postUrl = $postUrl;
        $this->active = false;
        $this->menu_class = $menu_class;

    }

    public function addChild(MenuItem $item) {
        $this->subItems[] = $item;
    }

    public function addChildren(array $children) {
        foreach($children as $child) {
            if($child instanceof MenuItem) {
                $this->addChild($child);
            }
        }
    }

    public function hasChildren() {
        if(count($this->subItems)) {
            return true;
        }

        return false;
    }

    public function setActive() {
        $this->active = true;
    }

    public function isActive() {
        return $this->active;
    }
}