<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonThumbnailTest extends TestCase
{
	public function testIsSupportedFileType()
	{
		$this->assertTrue(
			SpoonThumbnail::isSupportedFileType(dirname(realpath(__FILE__), 2) . '/tmp/spoon.jpg')
		);
	}
}
