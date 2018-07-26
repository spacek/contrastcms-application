<?php

namespace ContrastCms\Application;

class TreeItem extends stdClass {

    public $postId;
    public $postUrl;
    public $title;
    public $lang;
    public $active;
    public $is_unfolded;
    public $subItems = array();

    public function __construct($postId, $postUrl ,$title, $is_public, $lang, $is_unfolded = 0) {
        $this->postId = $postId;
        $this->title = $title;
        $this->lang = $lang;
        $this->postUrl = $postUrl;
        $this->isPublic = $is_public;
        $this->is_unfolded = $is_unfolded;

    }

    public function addChild(TreeItem $item) {
        $this->subItems[] = $item;
    }

    public function addChildren(array $children) {
        foreach($children as $child) {
            if($child instanceof TreeItem) {
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

    public function setActive($boolean = true) {
        $this->active = $boolean;
    }

    public function isActive() {
        return $this->active;
    }
}