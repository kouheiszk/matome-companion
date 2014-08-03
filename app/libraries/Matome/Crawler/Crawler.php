<?php

namespace Matome\Crawler;
use Goutte\Client;

class Crawler
{
	public function __construct() {
	}

	public function run() {
		$urls = $this->__getUrlList();
		return $urls;
	}

	/**
	 * Private methods
	 */

	private function __getUrlList() {

		$baseSearchUrl = 'http://ryokou-ya.co.jp/companion/search/';

		$client = new Client();
		$urls = array();

		for ($page = 1; $page < 20; ++$page) {
			$crawler = $client->request('GET', $baseSearchUrl . '?p=' . $page);
			$hotelCount = $crawler->filter('div.dispnum > span.hitnumber')->text();
			if (!is_numeric($hotelCount) || $hotelCount == 0) break;
			$crawler->filter('p.name > a')->each(function ($element) use ($baseSearchUrl, &$urls) {
				$path = $element->extract(array('href'))[0];
				$url = self::__createUrl($baseSearchUrl, $path);
				$urls[] = $url;
			});
		}

		return $urls;
	}

	/**
	 * 相対パスから絶対URLを返します
	 * @see http://blog.anoncom.net/2010/01/08/295.html/comment-page-1
	 *
	 * @param string $baseUrl
	 * @param string $relationalPath
	 *
	 * @return string
	 */
	private static function __createUrl($baseUrl = '', $relationalPath = '') {
		$parse = array(
			"scheme"   => null,
			"user"     => null,
			"pass"     => null,
			"host"     => null,
			"port"     => null,
			"query"    => null,
			"fragment" => null
		);
		$parse = parse_url($baseUrl);

		if (strpos($parse["path"], "/", (strlen($parse["path"]) - 1)) !== false) {
			$parse["path"] .= ".";
		}

		if (preg_match("#^https?\://#", $relationalPath)) {
			return $relationalPath;
		}
		else if(preg_match("#^/.*$#", $relationalPath)) {
			return $parse["scheme"] . "://" . $parse["host"] . $relationalPath;
		}
		else {
			$basePath = explode("/", dirname($parse["path"]));
			$relPath = explode("/", $relationalPath);
			foreach($relPath as $relDirName) {
				if ($relDirName == ".") {
					array_shift($basePath);
					array_unshift($basePath, "");
				}
				else if($relDirName == "..") {
					array_pop( $basePath );
					if (count($basePath) == 0) {
						$basePath = array("");
					}
				}
				else {
					array_push($basePath, $relDirName);
				}
			}
			$path = implode("/", $basePath);
			return $parse["scheme"] . "://" . $parse["host"] . $path;
		}

		return $baseUrl . $relationalPath;
	}
}