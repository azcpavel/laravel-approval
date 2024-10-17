<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalRequestApproversTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_request_approvers', function (Blueprint $table) {
			$table->foreignId('next_user_id')->nullable()->after('user_id');
			$table->tinyInteger('next_level_user')->default(0)->after('next_user_id');
			$table->foreign('next_user_id')->on(config('approval-config.user-table'))->references('id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ex_approval_request_approvers', function (Blueprint $table) {
			$table->dropForeign(['next_user_id']);
			$table->dropColumn('next_user_id');
			// $table->dropColumn('next_level_user');
		});
	}
}
