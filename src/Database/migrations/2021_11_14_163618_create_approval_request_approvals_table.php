<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestApprovalsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ex_approval_request_approvals', function (Blueprint $table) {
			$table->id();
			$table->foreignId('approval_id');
			$table->foreignId('approval_request_id');
			$table->foreignId('user_id');               
			$table->integer('prev_level')->nullable();
			$table->string('prev_level_title',190)->nullable();
			$table->integer('next_level')->nullable();
			$table->string('next_level_title',190)->nullable();
			$table->tinyInteger('is_approved')->default(0);
			$table->tinyInteger('is_rejected')->default(0);
			$table->tinyInteger('is_swaped')->default(0);
			$table->text('reason')->nullable();
			$table->timestamps();
			$table->foreign('approval_id')->on('ex_approvals')->references('id');
			$table->foreign('approval_request_id')->on('ex_approval_requests')->references('id');
			$table->foreign('user_id')->on(config('approval-config.user-table'))->references('id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ex_approval_request_approvals');
	}
}
