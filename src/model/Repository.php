<?php

namespace ContrastCms\Application;

use Nette\Database\Table\Selection;
use Nette\Http\Context;
use Nette\SmartObject;

abstract class Repository
{
	use SmartObject;

	/** @var \Nette\Database\Context */
	protected $connection;

	public function __construct(Context $db)
	{
		$this->connection = $db;
	}

	public function getInsertId()
	{
		return $this->getInsertId();
	}

	protected function getTable()
	{
		preg_match('#(\w+)Repository$#', get_class($this), $m);

		$tableName = lcfirst($m[1]);
		$tableName = ltrim(preg_replace('/[A-Z]/', '_$0', $tableName));
		return $this->connection->table(strtolower($tableName));
	}

	/**
	 * Alias to findAll()
	 * @return Selection
	 */
	public function find()
	{
		return $this->findAll();
	}

	public function findAll()
	{
		return $this->getTable();
	}

	public function findBy(array $by, $order)
	{
		if (trim($order) != "") {
			return $this->getTable()->where($by)->order($order);
		}

		return $this->getTable()->where($by);
	}

	public function findById($id)
	{
		return $this->getTable()->where(array(
			"id" => (int)$id
		));
	}

	public function deleteById($id)
	{
		return $this->getTable()->where(array(
			"id" => (int)$id
		))->delete();
	}

	public function countBy(array $by)
	{
		return $this->getTable()->where($by)->count();
	}

	public function count()
	{
		return $this->getTable()->count();
	}

	public function update(array $data, $id)
	{
		return $this->getTable()->where(array("id" => (int)$id))->update($data);
	}

	public function insert($data)
	{
		return $this->getTable()->insert($data);
	}
}