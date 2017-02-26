<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAgentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('agents', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('organization_id')->unsigned()->nullable();
			$table->string('field', 1024);
			$table->bigInteger('start_amount');
			$table->bigInteger('end_amount')->nullable();
			$table->text('comment', 65535)->nullable();
			$table->string('status', 32);
			$table->integer('region_id');
			$table->timestamps();
			$table->string('tender_statuses')->nullable();
			$table->string('kinds')->nullable();
			$table->string('regions')->nullable();
			$table->string('procedure_types')->nullable();
			$table->integer('guarantee')->nullable()->default(0);
			$table->integer('email_newsletter')->nullable()->default(0);
			$table->string('email_frequency')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('agents');
	}

}
