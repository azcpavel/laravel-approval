<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalRequestApproversTableAddAttachmentFlug extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_request_approvers', function (Blueprint $table) {
			$table->tinyInteger('need_attachment')->default(1)->after('next_level_user');
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
			$table->dropColumn('need_attachment');
		});
	}
}
