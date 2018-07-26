<?php

namespace ContrastCms\Application;

use Nette\Security\User;

class UserRepository extends Repository
{
	public function getAvailableGroupOptions(User $user)
	{
		if (isset($user->getIdentity()->group_id)) {
			$groups = $this->connection->table("group")->where("parent >= ?", $user->getIdentity()->group_id);
			$currentGroup = $this->connection->table("group")->where("id = ?", $user->getIdentity()->group_id)->fetch();
			$items = array();
			$items[$currentGroup->id] = $currentGroup->name;
			foreach ($groups as $group) {
				$items[$group->id] = $group->name;
			}

			return $items;
		}

		return array();
	}

	public function canModify($userId, User $currentUser)
	{

		if (isset($currentUser->getIdentity()->group_id)) {
			$userObject = $this->findById($userId)->fetch();
			if ($currentUser->getIdentity()->group_id <= $userObject->group_id) {
				return true;
			} else {
				return false;
			}

		}

		return false;
	}
}