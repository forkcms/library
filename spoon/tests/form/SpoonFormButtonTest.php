<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFormButtonTest extends TestCase
{
	/**
	 * @var	SpoonFormButton
	 */
	protected $btnSubmit, $btnReset, $btnSpecial;

	/**
	 * @var	SpoonForm
	 */
	protected $frm;

	public function setup(): void
	{
		$this->frm = new SpoonForm('button');
		$this->btnSubmit = new SpoonFormButton('submit', 'Submit', 'submit');
		$this->btnReset = new SpoonFormButton('reset', 'Reset', 'reset');
		$this->btnSpecial = new SpoonFormButton('special', 'Special', 'button');
		$this->frm->add($this->btnSubmit, $this->btnReset, $this->btnReset);
	}

	public function testGetDefaultValue()
	{
		$this->assertEquals('Submit', $this->btnSubmit->getDefaultValue());
		$this->assertEquals('Reset', $this->btnReset->getDefaultValue());
		$this->assertEquals('Special', $this->btnSpecial->getDefaultValue());
	}

	public function testAttributes()
	{
		$this->btnSubmit->setAttribute('rel', 'bauffman.jpg');
		$this->btnReset->setAttribute('rel', 'bauffman.jpg');
		$this->btnSpecial->setAttribute('rel', 'bauffman.jpg');
		$this->assertEquals('bauffman.jpg', $this->btnSubmit->getAttribute('rel'));
		$this->assertEquals('bauffman.jpg', $this->btnReset->getAttribute('rel'));
		$this->assertEquals('bauffman.jpg', $this->btnSpecial->getAttribute('rel'));
		$this->btnSubmit->setAttributes(['id' => 'specialID']);
		$this->btnReset->setAttributes(['id' => 'specialID']);
		$this->btnSpecial->setAttributes(['id' => 'specialID']);
		$this->assertEquals(['id' => 'specialID', 'name' => 'submit', 'class' => 'inputButton', 'rel' => 'bauffman.jpg'], $this->btnSubmit->getAttributes());
		$this->assertEquals(['id' => 'specialID', 'name' => 'reset', 'class' => 'inputButton', 'rel' => 'bauffman.jpg'], $this->btnReset->getAttributes());
		$this->assertEquals(['id' => 'specialID', 'name' => 'special', 'class' => 'inputButton', 'rel' => 'bauffman.jpg'], $this->btnSpecial->getAttributes());
	}
}
