<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFormHiddenTest extends TestCase
{
	/**
	 * @var	SpoonForm
	 */
	protected $frm;

	/**
	 * @var	SpoonFormHidden
	 */
	protected $hidHidden;

	public function setup(): void
	{
		$this->frm = new SpoonForm('hiddenfield');
		$this->hidHidden = new SpoonFormHidden('hidden', 'I am the default value');
		$this->frm->add($this->hidHidden);
	}

	public function testAttributes()
	{
		$this->hidHidden->setAttribute('rel', 'bauffman.jpg');
		$this->assertEquals('bauffman.jpg', $this->hidHidden->getAttribute('rel'));
		$this->hidHidden->setAttributes(['id' => 'specialID']);
		$this->assertEquals(['id' => 'specialID', 'name' => 'hidden', 'rel' => 'bauffman.jpg'], $this->hidHidden->getAttributes());
	}

	public function testIsFilled()
	{
		$this->assertEquals(false, $this->hidHidden->isFilled());
		$_POST['hidden'] = 'I am not empty';
		$this->assertTrue($this->hidHidden->isFilled());
		$_POST['hidden'] = ['foo', 'bar'];
		$this->assertTrue($this->hidHidden->isFilled());
	}

	public function testGetValue()
	{
		$_POST['form'] = 'hiddenfield';
		$_POST['hidden'] = 'But I am le tired';
		$this->assertEquals($_POST['hidden'], $this->hidHidden->getValue());
		$_POST['hidden'] = ['foo', 'bar'];
		$this->assertEquals('Array', $this->hidHidden->getValue());
	}

	public function testParse()
	{
		$_POST['form'] = 'hiddenfield';
		$_POST['hidden'] = 'But I am le tired';
		$this->assertEquals(
			'<input type="hidden" value="But I am le tired" id="hidden" name="hidden" />',
			$this->hidHidden->parse()
		);

		// Make sure we encode XSS payloads
		$_POST['hidden'] = 'But I am le tired\'"()%26%25<yes><ScRiPt%20>alert(1)</ScRiPt>';
		$this->assertEquals(
			'<input type="hidden" value="But I am le tired&#039;&quot;()%26%25&lt;yes&gt;&lt;ScRiPt%20&gt;alert(1)&lt;/ScRiPt&gt;" id="hidden" name="hidden" />',
			$this->hidHidden->parse()
		);
	}
}
