<?php

namespace ContrastCms\Application;

use Nette;

class Authenticator implements Nette\Security\IAuthenticator
{
	/** @var Nette\Database\Connection */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * @param array $credentials
	 * @return \Nette\Security\Identity|\Nette\Security\IIdentity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table('user')->where('username = ?', $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		if (Nette\Security\Passwords::verify($password, $row->password) === false) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		return new Nette\Security\Identity($row->id, null, $row->toArray());
	}
}