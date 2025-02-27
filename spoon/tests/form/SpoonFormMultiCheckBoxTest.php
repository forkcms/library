<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFormMultiCheckBoxTest extends TestCase
{
	/**
	 * @var	SpoonForm
	 */
	protected $frm;

	/**
	 * @var	SpoonFormMultiCheckbox
	 */
	protected $chkHobbies;

	public function setup(): void
	{
		$this->frm = new SpoonForm('multicheckbox');
		$hobbies[] = ['label' => 'Swimming', 'value' => 10];
		$hobbies[] = ['label' => 'Cycling', 'value' => 20, 'attributes' => ['rel' => 'bauffman.jpg']];
		$hobbies[] = ['label' => 'Running', 'value' => 30];
		$this->chkHobbies = new SpoonFormMultiCheckbox('hobbies', $hobbies, [10, 20]);
		$this->frm->add($this->chkHobbies);
	}

	public function testGetChecked()
	{
		$this->assertEquals(['10', '20'], $this->chkHobbies->getChecked());
	}

	public function testIsFilled()
	{
		$this->assertFalse($this->chkHobbies->isFilled());
		$_POST['hobbies'] = ['bimbo', 'tramp'];
		$this->assertFalse($this->chkHobbies->isFilled());
		$_POST['form'] = 'multicheckbox';
		$this->assertFalse($this->chkHobbies->isFilled());
		$_POST['hobbies'] = [20];
		$this->assertTrue($this->chkHobbies->isFilled());
		$_POST['hobbies'] = [20, 'bimbo', 'tramp'];
		$this->assertTrue($this->chkHobbies->isFilled());
		$_POST['hobbies'] = 'foobar';
		$this->assertFalse($this->chkHobbies->isFilled());
	}

	public function testGetValue()
	{
		$_POST['form'] = 'multicheckbox';
		$this->assertEquals([], $this->chkHobbies->getValue());
		$_POST['hobbies'] = ['bimbo', 'tramp'];
		$this->assertEquals([], $this->chkHobbies->getValue());
		$_POST['hobbies'] = ['10'];
		$this->assertEquals(['10'], $this->chkHobbies->getValue());
		$_POST['hobbies'] = ['10', 'bimbo', 'tramp'];
		$this->assertEquals(['10'], $this->chkHobbies->getValue());
		$_POST['hobbies'] = ['bimbo', 'tramp', '10', '30'];
		$this->assertEquals(['10', '30'], $this->chkHobbies->getValue());
		$this->chkHobbies->setAllowExternalData(true);
		$this->assertEquals(['bimbo', 'tramp', '10', '30'], $this->chkHobbies->getValue());
		$_POST['hobbies'] = 'foobar';
		$this->assertEquals([], $this->chkHobbies->getValue());
	}

	public function testNotSupplyingCorrectFormatThrowsException()
	{
		$values = ['12' => 'aaa', '132' => 'bbb', '32' => 'ccc'];
		$this->expectException('SpoonFormException');
		$c = new SpoonFormMultiCheckbox('test', $values);
	}

	public function testNotSupplyingLabelThrowsException()
	{
		$values = [
			['value' => 'aaa'],
			['value' => 'bbb'],
			['value' => 'ccc']
		];
		$this->expectException('SpoonFormException');
		$c = new SpoonFormMultiCheckbox('test', $values);
	}

	public function testNotSupplyingValueThrowsException()
	{
		$values = [
			['label' => 'aaa'],
			['label' => 'bbb'],
			['label' => 'ccc']
		];
		$this->expectException('SpoonFormException');
		$c = new SpoonFormMultiCheckbox('test', $values);
	}
}
