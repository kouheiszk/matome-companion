<?php

use Symfony\Component\DomCrawler\Crawler;

class RyokouyaHtmlParser {

	public function parseHotelPage(Crawler $crawler)
	{
		/**
		 * ホテル情報
		 */

		$hotel['prefecture'] = $crawler->filter('#yado .area a')->eq(0)->text();
		$hotel['location'] = $crawler->filter('#yado .area a')->eq(1)->text();

		$name = $crawler->filter('#yado h1')->text();
		if (preg_match('/(?P<name>.+)（(?P<yomi>.+)）/', $name, $matches) !== false && $matches) {
			$hotel['name'] = $matches['name'];
			$hotel['yomi'] = $matches['yomi'];
		}

		$hotel['address'] = $crawler->filter('#yado .icon_address')->siblings()->text();
		$hotel['access'] = $crawler->filter('#yado .icon_access')->siblings()->text();

		$hotel['evaluation'] = explode("\n", $crawler->filter('#yado .icon_oukan')->siblings()->text())[0];

		$hotel['catchcopy'] = $crawler->filter('#yado .profile .rightBox h2')->text();
		$hotel['description'] = $crawler->filter('#yado .profile .rightBox p')->text();

		$crawler->filter('#yado .profile .rightBox dl')->each(function (\Symfony\Component\DomCrawler\Crawler $node) use (&$hotel) {
			if ($node->filter('dt')->text() == 'お風呂') {
				$hotel['bath'] = explode('・', $node->filter('dd')->text());
			}
			else if ($node->filter('dt')->text() == '館内施設') {
				$hotel['facilities'] = explode('・', $node->filter('dd')->text());
			}
			else if ($node->filter('dt')->text() == 'お部屋') {
				$hotel['room'] = explode('・', $node->filter('dd')->text());
			}
		});

		$crawler->filter('#slideshow > div')->each(function (\Symfony\Component\DomCrawler\Crawler $node) use (&$hotel) {
			$image['description'] = $node->filter('.text')->text();
			$image['attribute'] = $node->filter('img')->attr('alt');
			$image['url'] = $node->filter('img')->attr('src');
			$hotel['images'][] = $image;
		});

		/**
		 * プラン情報
		 */

		$crawler->filter('#yado #TabbedPanels1 .planbox')->each(function (\Symfony\Component\DomCrawler\Crawler $node) use (&$hotel) {
			$plan = [];

			$typeClass = explode(" ", $node->attr('class'))[1];

			if ($typeClass == 'pink') $plan['type'] = 'pink';
			else if ($typeClass == 'cos') $plan['type'] = 'costume';
			else if ($typeClass == 'normal') $plan['type'] = 'normal';

			$plan['title'] = $node->filter('.plan_head h2')->text();
			$plan['description'] = $node->filter('.plan_body .plan_profile')->text();

			for ($i = 0, $count = $node->filter('.plan_body .plan_L dl.plan_detail dt')->count(); $i < $count; $i++) {
				$title = $node->filter('.plan_body .plan_L dl.plan_detail dt')->eq($i)->text();
				$content = $node->filter('.plan_body .plan_L dl.plan_detail dd')->eq($i)->text();

				if ($title == 'プラン内容') $plan['detail'] = $content;
				else if ($title == '適用期間') $plan['period'] = $content;
				else if ($title == 'ｺﾝﾊﾟﾆｵﾝ延長') $plan['extend'] = $content;
				else if ($title == 'ｺﾝﾊﾟﾆｵﾝ追加') $plan['addition'] = $content;
				else if ($title == '飲み放題内容') $plan['bottomless'] = $content;
				else if ($title == '飲み放題延長') $plan['bottomless_extend'] = $content;
				else if ($title == '当社限定特典') $plan['special'] = $content;
				else if ($title == 'オプション') $plan['option'] = $content;
				else if ($title == '備考') $plan['etc'] = $content;
			};

			$node->filter('.plan_body .plan_R img')->each(function (\Symfony\Component\DomCrawler\Crawler $node) use (&$plan) {
				$image['attribute'] = $node->attr('alt');
				$image['url'] = $node->attr('src');
				$plan['images'][] = $image;
			});


			/**
			 * 料金
			 *
			 * header label label ...
			 * header price price ...
			 * header price price ...
			 */

			$priceColumns = $node->filter('.plan_body .price table tr');
			for ($c = 0, $colNum = $priceColumns->count(); $c < $colNum; $c++) {
				$priceRows = $priceColumns->eq($c);
				for ($r = 1, $rowCount = $priceRows->children()->count(); $r < $rowCount; $r++) {
					$price[$r] = $priceRows->children()->eq($r)->text();

					// カンマ、円を取り除く
					if ($c != 0) {
						$price[$r] = str_replace([',', '円'], '', $price[$r]);
					}
				}

				$header = $priceRows->children()->eq(0)->text();
				if (preg_match('/(?P<customer>\d)[^\d]+(?P<companion>\d)/', $header, $matches) !== false && $matches) {
					$key = $matches['customer'] . ':' . $matches['companion'];
					$plan['price'][$key] = $price;
				}
				else {
					$plan['price']['header'] = $price;
				}
			}

			/**
			 * 特殊料金表
			 * TOOD: あとで対応する
			 */


			$hotel['plans'][] = $plan;
		});

		return $hotel;
	}
} 