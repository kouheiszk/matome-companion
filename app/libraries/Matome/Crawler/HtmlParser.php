<?php

use Symfony\Component\DomCrawler\Crawler;

class HtmlParser
{
	public static function parser($domain)
	{
		$parser = null;

		if ($domain == RyokouyaHtmlParser::$SITE_DOMAIN) {
			$parser = new RyokouyaHtmlParser();
		}

		if (empty($parser)) {
			throw new Exception('undefined domain');
		}

		return $parser;
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
	protected static function createUrl($baseUrl = '', $relationalPath = '')
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