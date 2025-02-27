<?php

use PHPUnit\Framework\TestCase;

/*
 * This test is not named xxxxxxxxTest.php so it will not be run when you run all tests
 *
 * Run manual: phpunit spoon/tests/database/SpoonDatabaseLargeDataSet.php
 *
 * Or
 *
 * Run once (to create data rows):
 * phpunit spoon/tests/database/SpoonDatabaseLargeDataSet.php
 *
 * And run counting test:
 * phpunit --filter testGetNumRows spoon/tests/database/SpoonDatabaseLargeDataSet.php
 *
 */

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonDatabaseLargeDataSet extends TestCase
{
	const NUMBER_OF_ROWS = 1000000;
	/**
	 * @var	SpoonDatabase
	 */
	protected $db;

	/**
	 *
	 */
	public function setup(): void
	{
		$this->db = new SpoonDatabase('mysql', '127.0.0.1', 'root', 'spoon', 'spoon_tests');
	}

	/**
	 * @throws SpoonDatabaseException
	 */
	public function testExecute()
	{
		// clear all tables
		if(count($this->db->getTables()) != 0) $this->db->drop($this->db->getTables());

		// create table users
		$this->db->execute("
			CREATE TABLE users (
			id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			username VARCHAR( 255 ) NOT NULL ,
			email VARCHAR( 255 ) NOT NULL ,
			developer ENUM( 'Y', 'N' ) NOT NULL
			) ENGINE = MYISAM;");

	}

	/**
	 * @throws SpoonDatabaseException
	 */
	public function testInsert()
	{
		$userRecord['username'] = 'username';
		$userRecord['email'] = 'username@domain.extension';
		$userRecord['developer'] = 'N';

		for($i = 0; $i < self::NUMBER_OF_ROWS; $i++) {
			$this->db->insert('users', $userRecord);
		}
	}

	/**
	 * @throws SpoonDatabaseException
	 */
	public function testGetNumRows()
	{
		$this->assertEquals(self::NUMBER_OF_ROWS, $this->db->getNumRows('SELECT id FROM users'));
		$this->assertEquals(10000, $this->db->getNumRows('SELECT id FROM users LIMIT ?', [10000]));
	}

}
