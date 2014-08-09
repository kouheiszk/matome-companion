<?php

use Symfony\Component\DomCrawler\Crawler;

class HtmlParser {

	private $domain;
	private $parser;

	private function __construct($domain) {
		if (empty($domain)) {
			throw new Exception('domain is not applied');
		}

		$this->domain = $domain;
		$this->parser = new RyokouyaHtmlParser();
	}

	public static function parser($domain) {
		return new HtmlParser($domain);
	}

	public function parseHotelPage(Crawler $crawler) {
		return $this->parser->parseHotelPage($crawler);
	}

	/**
	 * 宴会王国
	 */
} 