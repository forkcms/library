<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';

class SpoonFeedRSSTest extends TestCase
{
	public function testMain()
	{
		$rss = new SpoonFeedRSS('Spoon Library', 'http://feeds2.feedburner.com/spoonlibrary', 'Spoon Library - RSS feed.');
		self::assertInstanceOf(SpoonFeedRSS::class, $rss);
	}
}
