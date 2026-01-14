<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__DIR__, 3);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonTemplateTest extends TestCase
{
	public function testMain(): void
	{
		$tpl = new SpoonTemplate();
		self::assertInstanceOf(SpoonTemplate::class, $tpl);
	}

	public function testGetAssignedValue(): void
	{
		$tpl = new SpoonTemplate();
		$tpl->assign('name', 'value');
		$tpl->assign('list', ['name' => 'Erik Bauffman']);
		$this->assertEquals('value', $tpl->getAssignedValue('name'));
		$this->assertEquals(['name' => 'Erik Bauffman'], $tpl->getAssignedValue('list'));
		$this->assertEquals(null, $tpl->getAssignedValue('wtf-this-is-super-cool'));
	}
}
