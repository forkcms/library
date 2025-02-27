<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFileTest extends TestCase
{
	public function setup(): void
	{
		if(!defined('TMPPATH')) define('TMPPATH', dirname(realpath(__DIR__)) . '/tmp');

		$this->existingUrl = 'http://www.spoon-library.be/downloads/1.0.3/spoon-1.0.3.zip';
		$this->nonExistingUrl = 'http://wowbesturleverforspoonlibrary.dev/' . time() . '.txt';
		$this->destinationFile = TMPPATH . '/spoon.zip';
	}

	public function testDownload()
	{
		// download
		$this->assertTrue(SpoonFile::download($this->existingUrl, $this->destinationFile));

		// download again, but do not overwrite
		$this->assertFalse(SpoonFile::download($this->existingUrl, $this->destinationFile, false));
	}

	public function testDownloadFailure()
	{
		$this->expectException(SpoonFileException::class);
		SpoonFile::download($this->nonExistingUrl, $this->destinationFile);
	}
}
