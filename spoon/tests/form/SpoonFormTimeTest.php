<?php

use PHPUnit\Framework\TestCase;

date_default_timezone_set('Europe/Brussels');

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFormTimeTest extends TestCase
{
	/**
	 * @var	SpoonForm
	 */
	protected $frm;

	/**
	 * @var	SpoonFormTime
	 */
	protected $txtTime;

	public function setup(): void
	{
		$this->frm = new SpoonForm('timefield');
		$this->txtTime = new SpoonFormTime('time', date('H:i'));
		$this->frm->add($this->txtTime);
	}

	public function testGetDefaultValue()
	{
		$this->assertEquals(date('H:i'), $this->txtTime->getDefaultValue());
	}

	public function testErrors()
	{
		$this->txtTime->setError('You suck');
		$this->assertEquals('You suck', $this->txtTime->getErrors());
		$this->txtTime->addError(' cock');
		$this->assertEquals('You suck cock', $this->txtTime->getErrors());
		$this->txtTime->setError('');
		$this->assertEquals('', $this->txtTime->getErrors());
	}

	public function testAttributes()
	{
		$this->txtTime->setAttribute('rel', 'bauffman.jpg');
		$this->assertEquals('bauffman.jpg', $this->txtTime->getAttribute('rel'));
		$this->txtTime->setAttributes(['id' => 'specialID']);
		$this->assertEquals(['id' => 'specialID', 'name' => 'time','maxlength' => 5, 'class' => 'inputTimefield', 'rel' => 'bauffman.jpg'], $this->txtTime->getAttributes());
	}

	public function testIsFilled()
	{
		$this->assertFalse($this->txtTime->isFilled());
		$_POST['time'] = '14:55';
		$this->assertTrue($this->txtTime->isFilled());
		$_POST['name'] = ['foo', 'bar'];
		$this->assertTrue($this->txtTime->isFilled());
	}

	public function testIsValid()
	{
		$_POST['time'] = '';
		$this->assertFalse($this->txtTime->isValid());
		$_POST['time'] = 'Boobies';
		$this->assertFalse($this->txtTime->isValid());
		$_POST['time'] = '13:37';
		$this->assertTrue($this->txtTime->isValid());
		$_POST['time'] = '25:60';
		$this->assertFalse($this->txtTime->isValid());
		$_POST['time'] = 'pipi00:01asshole';
		$this->assertFalse($this->txtTime->isValid());
		$_POST['time'] = '1k:2b';
		$this->assertFalse($this->txtTime->isValid());
		$_POST['name'] = ['foo', 'bar'];
		$this->assertFalse($this->txtTime->isValid());
	}

	public function testGetTimestamp()
	{
		$_POST['time'] = '10:44';
		$this->assertEquals('25/10/2009 ' . date('H:i') . ':00', date('d/m/Y H:i:s', $this->txtTime->getTimestamp(2009, 10, 25)));
	}

	public function testGetValue()
	{
		$_POST['form'] = 'timefield';
		$_POST['time'] = '14:55';
		$this->assertEquals('14:55', $this->txtTime->getValue());
		$_POST['time'] = ['foo', 'bar'];
		$this->assertEquals('Array', $this->txtTime->getValue());
	}

	public function testParse()
	{
		$_POST['form'] = 'timefield';
		$_POST['time'] = '15:30';
		$this->assertEquals(
			'<input type="text" value="15:30" id="time" name="time" maxlength="5" class="inputTimefield" />',
			$this->txtTime->parse()
		);

		// Make sure we encode XSS payloads
		$_POST['time'] = '15:30\'"()%26%25<yes><ScRiPt%20>alert(1)</ScRiPt>';
		$this->assertEquals(
			'<input type="text" value="15:30&#039;&quot;()%26%25&lt;yes&gt;&lt;ScRiPt%20&gt;alert(1)&lt;/ScRiPt&gt;" id="time" name="time" maxlength="5" class="inputTimefield" />',
			$this->txtTime->parse()
		);
	}
}
