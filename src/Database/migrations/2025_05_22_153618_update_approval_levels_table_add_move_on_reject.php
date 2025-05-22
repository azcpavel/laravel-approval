<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalLevelsTableAddMoveOnReject extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_levels', function (Blueprint $table) {
			$table->tinyInteger('move_on_reject')->default(0)->after('properties');
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
			$table->dropColumn('move_on_reject');
		});
	}
}
