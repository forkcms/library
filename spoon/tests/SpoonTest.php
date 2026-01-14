<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 3);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonTest extends TestCase
{
	public function testGet()
	{
		$value = 'Speed spelled backwards is deeps.';
		$this->assertEquals(Spoon::set('stored_value', $value), $value);
		$this->assertEquals(Spoon::get('stored_value'), $value);
	}

	public function testGetFailure()
	{
		$this->expectException(SpoonException::class);
		$this->assertEquals('I have no idea what I am doing.', Spoon::get('my_custom_value'));
	}

	public function testExists()
	{
		// set value
		Spoon::set('spoon', new stdClass());

		// check value
		$this->assertTrue(Spoon::exists('spoon'));
		$this->assertFalse(spoon::exists('foobar'));
	}

	public function testSet()
	{
		// set value
		$value = ['Davy Hellemans', 'Tijs Verkoyen', 'Dave Lens', 'Matthias Mullie'];
		$this->assertEquals(Spoon::set('salad_fingers', $value), $value);

		// get rid of value
		Spoon::set('salad_fingers');
		$this->assertFalse(Spoon::exists('salad_fingers'));
	}
}
