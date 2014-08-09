<?php

class DatabaseSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('CronSitesTableSeeder');
		$this->command->info('`cron_sites` table seeded!');
	}
}

class CronSitesTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('cron_sites')->delete();
		CronSite::create([
			'domain' => 'ryokou-ya.co.jp',
			'base_url' => 'http://ryokou-ya.co.jp/companion/search/',
		]);
	}
}
