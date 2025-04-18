<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalRequestApprovalsTableAddCommentsFlag extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_request_approvals', function (Blueprint $table) {
			$table->tinyInteger('is_commented')->default(0)->after('is_resubmitted');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ex_approval_request_approvals', function (Blueprint $table) {
			$table->dropColumn('is_commented');
		});
	}
}
