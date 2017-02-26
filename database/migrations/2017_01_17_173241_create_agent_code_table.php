<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAgentCodeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('agent_code', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('agent_id')->unsigned();
			$table->integer('code_id')->unsigned();
			$table->unique(['agent_id','code_id'], 'agent_code_agent_id_codes_id_pk');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('agent_code');
	}

}
