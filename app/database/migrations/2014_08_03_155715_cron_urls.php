<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CronUrls extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cron_urls', function($t) {
			// auto increment id (primary key)
			$t->increments('id');

			$t->string('domain');
			$t->string('url');
			$t->dateTime('last_cron_date')->nullable();
			$t->boolean('last_cron_successed')->default(false);

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
		Schema::dropIfExists('cron_urls');
	}

}
