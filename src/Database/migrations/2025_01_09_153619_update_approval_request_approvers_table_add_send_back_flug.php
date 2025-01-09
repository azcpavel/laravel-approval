<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalRequestApproversTableAddSendBackFlug extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_request_approvers', function (Blueprint $table) {
			$table->tinyInteger('is_send_back')->default(0)->after('is_rejected');
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
			$table->dropColumn('is_send_back');
		});
	}
}
