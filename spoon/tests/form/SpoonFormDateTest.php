<?php

use PHPUnit\Framework\TestCase;

date_default_timezone_set('Europe/Brussels');

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFormDateTest extends TestCase
{
	/**
	 * @var	SpoonForm
	 */
	protected $frm;

	/**
	 * @var	SpoonFormDate
	 */
	protected $txtDate;

	public function setup(): void
	{
		$this->frm = new SpoonForm('datefield');
		$this->txtDate = new SpoonFormDate('date', strtotime('Last Monday'), 'd/m/Y');
		$this->frm->add($this->txtDate);
		$_POST['form'] = 'datefield';
	}

	public function testGetDefaultValue()
	{
		$this->assertEquals(date('d/m/Y', strtotime('Last Monday')), $this->txtDate->getDefaultValue());
	}

	public function testErrors()
	{
		$this->txtDate->setError('You suck');
		$this->assertEquals('You suck', $this->txtDate->getErrors());
		$this->txtDate->addError(' cock');
		$this->assertEquals('You suck cock', $this->txtDate->getErrors());
		$this->txtDate->setError('');
		$this->assertEquals('', $this->txtDate->getErrors());
	}

	public function testAttributes()
	{
		$this->txtDate->setAttribute('rel', 'bauffman.jpg');
		$this->assertEquals('bauffman.jpg', $this->txtDate->getAttribute('rel'));
		$this->txtDate->setAttributes(['id' => 'specialID']);
		$this->assertEquals(['id' => 'specialID', 'name' => 'date','maxlength' => 10, 'class' => 'inputDatefield', 'rel' => 'bauffman.jpg', 'data-mask' => 'dd/mm/yy'], $this->txtDate->getAttributes());
	}

	public function testIsFilled()
	{
		$this->assertFalse($this->txtDate->isFilled());
		$_POST['date'] = '12/10/2009';
		$this->assertTrue($this->txtDate->isFilled());
		$_POST['date'] = ['foo', 'bar'];
		$this->assertTrue($this->txtDate->isFilled());
	}

	public function testIsValid()
	{
		$this->assertFalse($this->txtDate->isValid());
		$_POST['date'] = '29/02/1997';
		$this->assertFalse($this->txtDate->isValid());
		$_POST['date'] = '29/02/2000';
		$this->assertTrue($this->txtDate->isValid());
		$_POST['date'] = '31/04/2009';
		$this->assertFalse($this->txtDate->isValid());
		$_POST['date'] = ['foo', 'bar'];
		$this->assertFalse($this->txtDate->isValid());
	}

	public function testGetTimestamp()
	{
		$_POST['date'] = '12/10/2009';
		$this->assertEquals('12/10/2009 12:13:14', date('d/m/Y H:i:s', $this->txtDate->getTimestamp(null, null, null, 12, 13, 14)));
		$this->assertEquals('12/10/2010 12:13:14', date('d/m/Y H:i:s', $this->txtDate->getTimestamp(2010, null, null, 12, 13, 14)));
		$this->assertEquals('12/11/2009 12:13:14', date('d/m/Y H:i:s', $this->txtDate->getTimestamp(null, 11, null, 12, 13, 14)));
		$this->assertEquals('25/10/2009 12:13:14', date('d/m/Y H:i:s', $this->txtDate->getTimestamp(null, null, 25, 12, 13, 14)));

		$_POST['date'] = ['foo', 'bar'];
		$this->assertEquals(date('Y-m-d H:i:s'), date('Y-m-d H:i:s', $this->txtDate->getTimestamp()));
	}

	public function testGetValue()
	{
		$_POST['form'] = 'datefield';
		$_POST['date'] = '12/10/2009';
		$this->assertEquals('12/10/2009', $this->txtDate->getValue());

		$_POST['date'] = ['foo', 'bar'];
		$this->assertEquals('Array', $this->txtDate->getValue());
	}

	public function testDateFormatsLong()
	{
		$formats = [
			'j F Y',
			'D j F Y',
			'l j F Y',
			'j F, Y',
			'D j F, Y',
			'l j F, Y',
			'd F Y',
			'd F, Y',
			'F j Y',
			'D F j Y',
			'l F j Y',
			'F d, Y',
			'D F d, Y',
			'l F d, Y',
		];
		$this->loopOverFormats($formats);
	}

	/**
	 * Loop over formats and test if they work.
	 *
	 * @param array $formats
	 */
	private function loopOverFormats(array $formats)
	{
		foreach ($formats as $format) {
			$timestamp = strtotime('Last Monday');
			$date = date($format, $timestamp);

			// set up the form
			$form = new SpoonForm('formattedDateFieldForm');
			$txtFormattedDate = new SpoonFormDate('formattedDate', $timestamp, $format);
			$form->add($txtFormattedDate);

			// the actual test
			$this->assertEquals($date, $txtFormattedDate->getValue(), $format . ' failed');
		}
	}

	public function testDateFormatsShort()
	{
		$formats = [
			'j/n/Y',
			'j-n-Y',
			'j.n.Y',
			'n/j/Y',
			'n/j/Y',
			'n/j/Y',
			'd/m/Y',
			'd-m-Y',
			'd.m.Y',
			'm/d/Y',
			'm-d-Y',
			'm.d.Y',
			'j/n/y',
			'j-n-y',
			'j.n.y',
			'n/j/y',
			'n-j-y',
			'n.j.y',
			'd/m/y',
			'd-m-y',
			'd.m.y',
			'm/d/y',
			'm-d-y',
			'm.d.y',
		];
		$this->loopOverFormats($formats);
	}

	public function testParse()
	{
		$_POST['date'] = '12/10/2026';
		$this->assertEquals(
			'<input type="text" value="12/10/2026" id="date" name="date" maxlength="10" data-mask="dd/mm/yy" class="inputDatefield" />',
			$this->txtDate->parse()
		);

		// Make sure we encode XSS payloads
		$_POST['date'] = '12/10/2026\'"()%26%25<yes><ScRiPt%20>alert(1)</ScRiPt>';
		$this->assertEquals(
			'<input type="text" value="12/10/2026&#039;&quot;()%26%25&lt;yes&gt;&lt;ScRiPt%20&gt;alert(1)&lt;/ScRiPt&gt;" id="date" name="date" maxlength="10" data-mask="dd/mm/yy" class="inputDatefield" />',
			$this->txtDate->parse()
		);
	}
}
