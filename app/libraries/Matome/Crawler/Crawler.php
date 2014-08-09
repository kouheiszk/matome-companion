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
		$status = $this->__retrieveCronUrls();
		if (!$status) return false;

//		$status = $this->__retrieveHotelInformation();
//		if (!$status) return false;

		return $status;
	}

	/**
	 * Private methods
	 */

	private function __retrieveHotelInformation()
	{
		$cronUrls = CronUrl::where('will_cron', '=', true)
			->orderBy('last_cron_successed', 'asc')
			->orderBy('last_cron_date', 'asc')
			->limit(self::$FETCH_LIMIT)
			->get();

		$client = new Client();
		$cronUrls->each(function (CronUrl $cronUrl) use ($client) {
			$hotel = HtmlParser::parser($cronUrl->domain)->parseHotelPage($client->request('GET', $cronUrl->url));
			// save hotel to database
			// update cron date
		});

		return $cronUrls;
	}

	private function __retrieveCronUrls()
	{
		$cronSiteUrls = CronSite::where('will_cron', '=', true)->get();

		$result = [];

		$cronSiteUrls->each(function (CronSite $cronSite) use (&$result) {
			$savedUrls = CronUrl::where('domain', '=', $cronSite->domain)
				->get()
				->toBase()
				->map(function (CronUrl $cronUrl) {
				return $cronUrl->url;
			});

			$hotelPageUrls = HtmlParser::parser($cronSite->domain)->findHotelPageUrls($cronSite->base_url);
			foreach ($hotelPageUrls as $url) {
				if ($savedUrls->contains($url)) continue;
				CronUrl::create([
					'domain' => $cronSite->domain,
					'url' => $url,
				]);
			}
		});

		return $result;
	}
}