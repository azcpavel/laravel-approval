<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalLevelsTableAddProperties extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_levels', function (Blueprint $table) {
			$table->text('properties')->nullable()->after('level');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ex_approval_levels', function (Blueprint $table) {
			$table->dropColumn('properties');
		});
	}
}
