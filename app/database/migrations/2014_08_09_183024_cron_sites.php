<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CronSites extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cron_sites', function($t) {
			// auto increment id (primary key)
			$t->increments('id');

			$t->string('domain');
			$t->string('base_url');
			$t->dateTime('last_cron_date')->nullable();
			$t->boolean('last_cron_successed')->default(false);
			$t->boolean('will_cron')->default(true);

			// created_at, updated_at DATETIME
			$t->timestamps();

			$t->engine = 'InnoDB';
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cron_sites');
	}

}
