<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonLocaleTest extends TestCase
{
	public function testGetAvailableLanguages()
	{
		$this->assertEquals(['de', 'en', 'es', 'fr', 'nl', 'it'], SpoonLocale::getAvailableLanguages());
	}

	public function testGetConjunction()
	{
		$this->assertEquals('and', SpoonLocale::getConjunction('And', 'en'));
		$this->assertEquals('y', SpoonLocale::getConjunction('And', 'es'));
		$this->assertEquals('und', SpoonLocale::getConjunction('And', 'de'));
		$this->assertEquals('et', SpoonLocale::getConjunction('And', 'fr'));
		$this->assertEquals('en', SpoonLocale::getConjunction('And', 'nl'));
	}
}
