<?php

namespace ContrastCms\Application;

use Nette\Security\User;

class ModuleRepository extends Repository
{
    public function getEnabled()
    {
        $modules = $this->findBy(array('enabled' => 1), 'id ASC');

        if ($modules) {
            return $modules;
        }

        return array();
    }

    public function getTopMenu(User $user = null, $parentId = null)
    {
        $modules = $this->findBy(array('enabled' => 1, 'in_menu' => 1, 'parent_id' => $parentId), 'id ASC');

        if ($modules) {
            return $modules;
        }

        return array();
    }
}
