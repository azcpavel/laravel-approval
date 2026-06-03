<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalRequestApproversTableAddCustomAction extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_request_approvers', function (Blueprint $table) {
			$table->string('custom_action')->nullable();
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
			$table->dropColumn('custom_action');
		});
	}
}
