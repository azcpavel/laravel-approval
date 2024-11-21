<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApprovalRequestApprovalsTableAddResubmittedFlug extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ex_approval_request_approvals', function (Blueprint $table) {
			$table->tinyInteger('is_resubmitted')->default(0)->after('is_swaped');
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
			$table->dropColumn('is_resubmitted');
		});
	}
}
