<?php

namespace ContrastCms\Application;

use Nette;

class CrudRepository
{
	/** @var Nette\Database\Context */
	protected $connection;

	public function table($name)
	{
		return $this->connection->table($name);
	}

	public function getTable($name)
	{
		return $this->table($name);
	}

	public function query($sql) {
		return $this->connection->query($sql);
	}

	public function __construct(Nette\Database\Context $db)
	{
		$this->connection = $db;
	}
}