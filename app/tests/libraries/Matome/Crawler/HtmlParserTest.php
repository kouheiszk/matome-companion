<?php

class HtmlParserTest extends TestCase
{
	public function testCreateUrl()
	{
		Closure::bind(function () {
			$baseUrl = 'http://example.com/hoge/fuga/moge.html';
			$this->assertEquals(HtmlParser::createUrl($baseUrl, 'http://example.com/test.html'), 'http://example.com/test.html');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, '/'), 'http://example.com/');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, '/index.html'), 'http://example.com/index.html');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, '/foo/bar/baz/'), 'http://example.com/foo/bar/baz/');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, './foo/bar/baz'), 'http://example.com/hoge/fuga/foo/bar/baz');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, '../../../foo/bar/baz/index.html'), 'http://example.com/foo/bar/baz/index.html');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, 'foo/bar/baz.html'), 'http://example.com/hoge/fuga/foo/bar/baz.html');
			$this->assertEquals(HtmlParser::createUrl($baseUrl, 'foo/bar/baz/../index.html'), 'http://example.com/hoge/fuga/foo/bar/index.html');
		}, $this, 'HtmlParser')->__invoke();
	}
}
