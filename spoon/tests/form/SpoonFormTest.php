<?php

use PHPUnit\Framework\TestCase;

date_default_timezone_set('Europe/Brussels');

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFormTest extends TestCase
{
	public function testMain()
	{
		$frm = new SpoonForm('name', 'action');
		$frm->addButton('submit', 'submit');
		self::assertInstanceOf(SpoonFormButton::class, $frm->getField('submit'));
		$frm->addCheckbox('agree', true);
		self::assertInstanceOf(SpoonFormCheckbox::class, $frm->getField('agree'));
		$frm->addDate('date', time(), 'd/m/Y');
		self::assertInstanceOf(SpoonFormDate::class, $frm->getField('date'));
		$frm->addDropdown('author', [1 => 'Davy', 'Tijs', 'Dave'], 1);
		self::assertInstanceOf(SpoonFormDropdown::class, $frm->getField('author'));
		$frm->addFile('pdf');
		self::assertInstanceOf(SpoonFormFile::class, $frm->getField('pdf'));
		$frm->addImage('image');
		self::assertInstanceOf(SpoonFormImage::class, $frm->getField('image'));
		$frm->addHidden('cant_see_me', 'whoop-tie-doo');
		self::assertInstanceOf(SpoonFormHidden::class, $frm->getField('cant_see_me'));
		$frm->addMultiCheckbox('hobbies', [['label' => 'Swimming', 'value' => 'swimming']]);
		self::assertInstanceOf(SpoonFormMultiCheckbox::class, $frm->getField('hobbies'));
		$frm->addPassword('top_sekret', 'stars-and-stripes');
		self::assertInstanceOf(SpoonFormPassword::class, $frm->getField('top_sekret'));
		$frm->addRadiobutton('gender', [['label' => 'Male', 'value' => 'male']]);
		self::assertInstanceOf(SpoonFormRadiobutton::class, $frm->getField('gender'));
		$frm->addTextarea('message', 'big piece of text');
		self::assertInstanceOf(SpoonFormTextarea::class, $frm->getField('message'));
		$frm->addText('email', 'something@example.org');
		self::assertInstanceOf(SpoonFormText::class, $frm->getField('email'));
		$frm->addText('now', date('H:i'));
		self::assertInstanceOf(SpoonFormText::class, $frm->getField('now'));
	}

	public function  testExistsField()
	{
		// setup
		$frm = new SpoonForm('name', 'action');
		$frm->addButton('submit', 'submit');

		// checks
		$this->assertTrue($frm->existsField('submit'));
		$this->assertFalse($frm->existsField('custom_field'));
	}
}
