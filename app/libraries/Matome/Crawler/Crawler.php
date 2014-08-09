<?php

use Goutte\Client;

class Crawler
{
	private static $FETCH_LIMIT = 2;

	public function __construct()
	{
	}

	public function run()
	{
//		$status = $this->__retrieveCronUrls();
//		if (!$status) return false;

		$status = $this->__retrieveHotelInformation();
		if (!$status) return false;

		return $status;
	}

	/**
	 * Private methods
	 */

	private function __retrieveHotelInformation()
	{
		$cronUrls = CronUrl::orderBy('last_cron_successed', 'asc')->orderBy('last_cron_date', 'asc')->limit(self::$FETCH_LIMIT)->get();
		$cronUrl = $cronUrls->get(0);
		$client = new Client();
		return HtmlParser::parser($cronUrl->domain)->parseHotelPage($client->request('GET', $cronUrl->url));
	}

	private function __retrieveCronUrls()
	{
		$cronUrls = CronUrl::all()->toBase()->map(function (CronUrl $cronUrl) {
			return $cronUrl->url;
		});

		$urls = $this->__getUrlList();
		foreach ($urls as $k => $url) {
			if ($cronUrls->contains($url)) continue;
			$domain = parse_url($url, PHP_URL_HOST);
			CronUrl::create(['domain' => $domain, 'url' => $url]);
		}

		return ($urls != null);
	}

	private function __getUrlList()
	{
		$baseSearchUrl = 'http://ryokou-ya.co.jp/companion/search/';

		$client = new Client();
		$urls = array();

		try {
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
		} catch (Exception $e) {
			// HTMLの構造が変わっている可能性があるため、エラーを記録する
			Log::error($e);

			return null;
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
	private static function __createUrl($baseUrl = '', $relationalPath = '')
	{
		$parse = array("scheme" => null, "user" => null, "pass" => null, "host" => null, "port" => null, "query" => null, "fragment" => null);
		$parse = parse_url($baseUrl);

		if (strpos($parse["path"], "/", (strlen($parse["path"]) - 1)) !== false) {
			$parse["path"] .= ".";
		}

		if (preg_match("#^https?\://#", $relationalPath)) {
			return $relationalPath;
		}
		else if (preg_match("#^/.*$#", $relationalPath)) {
			return $parse["scheme"] . "://" . $parse["host"] . $relationalPath;
		}
		else {
			$basePath = explode("/", dirname($parse["path"]));
			$relPath = explode("/", $relationalPath);
			foreach ($relPath as $relDirName) {
				if ($relDirName == ".") {
					array_shift($basePath);
					array_unshift($basePath, "");
				}
				else if ($relDirName == "..") {
					array_pop($basePath);
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