<?php
/**
 * Created by PhpStorm.
 * User: kouhei
 * Date: 2014/08/03
 * Time: 0:05
 */

namespace Matome\Crawler;

class CrawlerTest extends \PHPUnit_Framework_TestCase {

	public function test__createUrl() {
		\Closure::bind(function(){
			$crawler = new Crawler();

			$baseUrl = 'http://example.com/hoge/fuga/moge.html';

			$this->assertEquals($crawler->__createUrl($baseUrl, 'http://example.com/test.html'),    'http://example.com/test.html');
			$this->assertEquals($crawler->__createUrl($baseUrl, '/'),                               'http://example.com/');
			$this->assertEquals($crawler->__createUrl($baseUrl, '/index.html'),                     'http://example.com/index.html');
			$this->assertEquals($crawler->__createUrl($baseUrl, '/foo/bar/baz/'),                   'http://example.com/foo/bar/baz/');
			$this->assertEquals($crawler->__createUrl($baseUrl, './foo/bar/baz'),                   'http://example.com/hoge/fuga/foo/bar/baz');
			$this->assertEquals($crawler->__createUrl($baseUrl, '../../../foo/bar/baz/index.html'), 'http://example.com/foo/bar/baz/index.html');
			$this->assertEquals($crawler->__createUrl($baseUrl, 'foo/bar/baz.html'),                'http://example.com/hoge/fuga/foo/bar/baz.html');
			$this->assertEquals($crawler->__createUrl($baseUrl, 'foo/bar/baz/../index.html'),       'http://example.com/hoge/fuga/foo/bar/index.html');

		}, $this, '\Matome\Crawler\Crawler')->__invoke();
	}
}
