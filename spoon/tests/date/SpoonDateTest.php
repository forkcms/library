<?php

use PHPUnit\Framework\TestCase;

date_default_timezone_set('Europe/Brussels');

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonDateTest extends TestCase
{
	public function testGetDate()
	{
		$this->assertEquals(date('Y-m-d H:i'), SpoonDate::getDate('Y-m-d H:i'));
		$this->assertEquals(SpoonDate::getDate('l j F Y', mktime(13, 20, 0, 8, 3, 1983)), 'Wednesday 3 August 1983');
	}
}
