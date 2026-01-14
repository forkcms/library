<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonDatabaseTest extends TestCase
{
	/**
	 * @var	SpoonDatabase
	 */
	protected $db;

	public function setup(): void
	{
		// create database object
		$this->db = new SpoonDatabase('mysql', '127.0.0.1', 'root', 'spoon', 'spoon_tests');
	}

	public function testExecute()
	{
		// create database
		try {
			$this->db->execute('CREATE DATABASE IF NOT EXISTS spoon_tests');
		} catch (SpoondatabaseException) {
			$this->fail('You should manually create a database "spoon_tests"');
		}

		// clear all tables
		if (count($this->db->getTables()) != 0) {
			$this->db->drop($this->db->getTables());
		}

		// create table users
		$this->db->execute(
			"
			CREATE TABLE `users` (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`username` VARCHAR( 255 ) NOT NULL ,
			`email` VARCHAR( 255 ) NOT NULL ,
			`developer` ENUM( 'Y', 'N' ) NOT NULL
			) ENGINE = MYISAM;"
		);

		// create dummy table
		$this->db->execute(
			"
			CREATE TABLE `test` (
			`id` int(11) NOT NULL auto_increment,
			`value` varchar(255) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM;"
		);

		// create table with datetime
		$this->db->execute(
			"
			CREATE TABLE `date_test` (
			`id` int(11) NOT NULL auto_increment,
			`date` DATETIME NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM;"
		);

		// do nothing
		$this->db->execute('SELECT * FROM users LIMIT ?', 10);
		$this->db->execute('SELECT * FROM users limit :limit', [':limit' => 10]);
		$this->expectException(PDOException::class);
		$this->db->execute('SELECT * FROM non_existing limit :limit', [':limit' => 10]);
	}

	/**
	 * @depends testExecute
	 */
	public function testDrop()
	{
		// table 'test' occures in the list of tables
		$this->assertEquals(true, in_array('test', $this->db->getTables()));

		// drop test
		$this->db->drop('test');

		// table 'test' no longer exists in the list of tables
		$this->assertEquals(false, in_array('test', $this->db->getTables()));
	}

	/**
	 * @depends testExecute
	 */
	public function testGetTables()
	{
		$this->assertEquals(['date_test', 'users'], $this->db->getTables());
	}

	/**
	 * @depends testExecute
	 */
	public function testDebug()
	{
		// disable debug
		$this->db->setDebug(false);
		$this->assertEquals(false, $this->db->getDebug());

		// enable debug
		$this->db->setDebug(true);
		$this->assertEquals(true, $this->db->getDebug());
	}

	/**
	 * @depends testExecute
	 */
	public function testInsert()
	{
		// data
		$aData['username'] = 'username';
		$aData['email'] = 'username@domain.extension';
		$aData['developer'] = 'N';

		// insert one record
		$this->db->insert('users', $aData);

		// insert 1000 records
		for ($i = 0; $i < 1000; $i++) {
			$array[$i] = $aData;
		}
		$this->db->insert('users', $array);
		$aData['id'] = 1;
		$this->expectException(PDOException::class);
		$this->db->insert('users', $aData);
	}

	/**
	 * @depends testExecute
	 */
	public function testInsertDate()
	{
		// data
		$aData['date'] = new DateTime();
		$this->db->insert('date_test', $aData);

		// multiple rows data
		$aData = [
			[
				'date' => new DateTime(),
			],
			[
				'date' => new DateTime(),
			],
		];
		$this->db->insert('date_test', $aData);
		$this->expectException(PDOException::class);
		$aData = [
			'id' => 1,
			'date' => new DateTime(),
		];
		$this->db->insert('date_test', $aData);
	}

	/**
	 * @depends testExecute
	 */
	public function testGetNumRows()
	{
		$this->assertEquals(1001, $this->db->getNumRows('SELECT id FROM users'));
		$this->assertEquals(1001, $this->db->getNumRows('SELECT id FROM users WHERE id != ?', 1337));
		$this->assertEquals(1001, $this->db->getNumRows('SELECT id FROM users WHERE id != :id', [':id' => 1337]));
		$this->assertEquals(1001, $this->db->getNumRows('SELECT id FROM users LIMIT ?', [9999]));
		$this->assertEquals(1001, $this->db->getNumRows('SELECT id FROM users LIMIT :limit', [':limit' => 9999]));
	}

	/**
	 * @depends testExecute
	 */
	public function testGetEnumValues()
	{
		$this->assertEquals(['Y', 'N'], $this->db->getEnumValues('users', 'developer'));
	}

	/**
	 * @depends testExecute
	 */
	public function testGetVar()
	{
		$this->assertEquals('1001', $this->db->getVar('SELECT COUNT(id) FROM users'));
		$this->assertEquals('1001', $this->db->getVar('SELECT COUNT(id) FROM users WHERE id != ?', 1337));
		$this->assertEquals(
			'1001',
			$this->db->getVar('SELECT COUNT(id) FROM users WHERE id != :id', [':id' => 1337])
		);
		$this->assertEquals('1', $this->db->getVar('SELECT id FROM users ORDER BY id ASC LIMIT 1'));
		$this->assertEquals('1', $this->db->getVar('SELECT id FROM users ORDER BY id ASC LIMIT ?', 1));
		$this->assertEquals('1', $this->db->getVar('SELECT id FROM users ORDER BY id ASC LIMIT ?', [1]));
		$this->assertEquals(
			'1',
			$this->db->getVar('SELECT id FROM users ORDER BY id ASC LIMIT :limit', [':limit' => 1])
		);
	}

	/**
	 * @depends testExecute
	 */
	public function testGetPairs()
	{
		$this->assertEquals(10, count($this->db->getPairs('SELECT id, username FROM users LIMIT 10;')));
		$this->assertEquals(
			10,
			count($this->db->getPairs('SELECT id, username FROM users WHERE id != ? LIMIT 10', 1337))
		);
		$this->assertEquals(
			10,
			count($this->db->getPairs('SELECT id, username FROM users WHERE id != ? LIMIT ?', [1337, 10]))
		);
		$this->assertEquals(
			10,
			count($this->db->getPairs('SELECT id, username FROM users WHERE id != :id LIMIT 10', [':id' => 1337]))
		);
		$this->assertEquals(
			10,
			count(
				$this->db->getPairs(
					'SELECT id, username FROM users WHERE id != :id LIMIT :limit',
					[':id' => 1337, ':limit' => 10]
				)
			)
		);
	}

	/**
	 * @depends testExecute
	 */
	public function testDelete()
	{
		// delete record 1 and 1001
		$this->db->delete('users', 'id = ?', 1);
		$this->db->delete('users', 'id = :id', [':id' => 1001]);

		// 999 records should remain
		$this->assertEquals(999, $this->db->getVar('SELECT COUNT(id) FROM users'));
	}

	/**
	 * @depends testExecute
	 */
	public function testUpdate()
	{
		// no record with id 1337
		$this->assertEquals(0, $this->db->getNumRows('SELECT id FROM users WHERE id = ?', 1337));

		// update record
		$this->db->update(
			'users',
			['id' => 1337, 'username' => 'Bauffman', 'email' => 'erik@bauffman.be', 'developer' => 'Y'],
			'id = ?',
			2
		);

		// 1 record with id 1337
		$this->assertEquals(1, $this->db->getNumRows('SELECT id FROM users WHERE id = ?', 1337));

		// update record
		$this->db->update(
			'users',
			['id' => 1337],
			'id = :leet AND id != :bauffman',
			[':leet' => 1337, ':bauffman' => 291]
		);
	}

	/**
	 * @depends testExecute
	 */
	public function testUpdateDate()
	{
		// data
		$aData['date'] = new DateTime();
		self::assertTrue(is_int($this->db->update('date_test', $aData)));
	}

	/**
	 * @depends testExecute
	 */
	public function testOptimize()
	{
		self::assertIsArray($this->db->optimize('users'));
		self::assertIsArray($this->db->optimize(['users']));
	}

	/**
	 * @depends testExecute
	 */
	public function testGetColumn()
	{
		$this->assertEquals(10, count($this->db->getColumn('SELECT username FROM users LIMIT 10')));
	}

	/**
	 * @depends testExecute
	 */
	public function testGetQueries()
	{
		$this->db->setDebug(true);
		$this->db->execute('SELECT id FROM users');
		$this->assertEquals(1, count($this->db->getQueries()));
	}

	/**
	 * @depends testExecute
	 */
	public function testGetRecord()
	{
		$data['username'] = 'Bauffman';
		$data['email'] = 'erik@bauffman.be';
		$data['developer'] = 'Y';

		$this->assertEquals(
			$data,
			$this->db->getRecord('SELECT username, email, developer FROM users WHERE id = ?', 1337)
		);
		$this->assertEquals(
			$data,
			$this->db->getRecord('SELECT username, email, developer FROM users WHERE id = :id', [':id' => 1337])
		);
	}

	/**
	 * @depends testExecute
	 */
	public function testGetRecords()
	{
		$this->assertEquals(100, count($this->db->getRecords('SELECT * FROM users WHERE id != ? LIMIT 100', 1337)));
		$this->assertEquals(
			100,
			count($this->db->getRecords('SELECT * FROM users WHERE id != :id LIMIT 100', [':id' => 1337]))
		);
	}

	/**
	 * @depends testExecute
	 */
	public function testTruncate()
	{
		$this->db->truncate('users');
		$this->db->truncate(['users']);

		$this->assertEquals(0, $this->db->getNumRows('SELECT id FROM users'));
	}
}
