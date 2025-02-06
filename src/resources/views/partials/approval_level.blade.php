<div class="col-12 approval_level_item mt-3">
	<div class="form-group approval_level_no">
		<h5>Level <span>APPROVAL_LEVEL</span> <button type="button" class="btn btn-danger btn-sm float-right approval_level_item_remove">Remove</button></h5>
		<hr>
	</div>
	<div class="form-group">
		<label for="approval_title">Title<span class="text-danger position-relative">*</span></label>
		<input class="form-control" type="text" name="approval_title[]" placeholder="Level Title" required>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_level">Level<span class="text-danger position-relative">*</span></label>
		<input class="form-control" type="number" min="1" max="100" name="approval_level[]" placeholder="Level" required>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_properties">Properties</label>
		<input class="form-control" type="string" name="approval_properties[]" placeholder="Properties">
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_flex">Flexible<span class="text-danger position-relative">*</span></label>
		<input class="form-control" type="number" min="0" max="100" name="approval_flex[]" value="0" placeholder="Flexible" required>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_group_notification">Group Notification<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_group_notification[]">
			<option value="0">No</option>
			<option value="1">Yes</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_next_notification">Next Level Notification<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_next_notification[]">
			<option value="1">Yes</option>
			<option value="0">No</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_next_notification">Next Level User Selection<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_next_user[]">
			<option value="0">No</option>
			<option value="1">Yes</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_need_attachment">Need Attachement<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_need_attachment[]">
			<option value="1">Yes</option>
			<option value="0">No</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_approve_reason">Approve Reason Mandatory<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_approve_reason[]">
			<option value="0">No</option>
			<option value="1">Yes</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_reject_reason">Reject Reason Mandatory<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_reject_reason[]">
			<option value="1">Yes</option>
			<option value="0">No</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_data_mapped">Data Mapped<span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_data_mapped[]">
			<option value="1">Yes</option>
			<option value="0">No</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_action_type">Action Type<span class="text-danger position-relative">*</span></label>
		<select class="form-control approval_action_type" name="approval_action_type[]">												
			<option value="0">None</option>
			<option value="1">Class Path</option>
			<option value="2">Redirect URL</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_action_data" class="action-class">Action Class Data</label>		
		<label for="approval_action_data" class="action-url">Action URL Data</label>		
		<div class="input-group action-class">
			<input class="form-control" type="text" name="approval_action_class_before_path[]" placeholder="Before Namespace">
			<input class="form-control" type="text" name="approval_action_class_before_method[]" placeholder="Before Method">
		</div>
		<div class="input-group action-class mt-2">
			<input class="form-control" type="text" name="approval_action_class_approve_path[]" placeholder="Approve Namespace">
			<input class="form-control" type="text" name="approval_action_class_approve_method[]" placeholder="Approve Method">
		</div>
		<div class="input-group action-class mt-2">
			<input class="form-control" type="text" name="approval_action_class_send_back_path[]" placeholder="Send Back Namespace">
			<input class="form-control" type="text" name="approval_action_class_send_back_method[]" placeholder="Send Back Method">
		</div>
		<div class="input-group action-class mt-2">
			<input class="form-control" type="text" name="approval_action_class_reject_path[]" placeholder="Reject Namespace">
			<input class="form-control" type="text" name="approval_action_class_reject_method[]" placeholder="Reject Method">
		</div>		
		<div class="input-group action-url mt-2">
			<input class="form-control" type="text" name="approval_action_url_approve_route[]" placeholder="Approve Route Name">
			<input class="form-control" type="text" name="approval_action_url_approve_param[]" placeholder="JSON {'param_name':'main_model_column'}">
		</div>
		<div class="input-group action-url mt-2">
			<input class="form-control" type="text" name="approval_action_url_reject_route[]" placeholder="Reject Route Name">
			<input class="form-control" type="text" name="approval_action_url_reject_param[]" placeholder="JSON {'param_name':'main_model_column'}">
		</div>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_action_frequency">Action Frequency</label>
		<select class="form-control" name="approval_action_frequency[]">												
			<option value="0">None</option>
			<option value="1">Every Time</option>
			<option value="2">Final</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_status_fields">Status Fields</label>
		<div class="level_status_fields_approve_div">
			<div class="input-group level_status_fields_approve_div_item mb-2">				
				<div class="input-group-prepend">
				    <span class="input-group-text">Approve</span>
				</div>
				<input class="form-control" type="text" name="approval_status_fields_approve_column[]" placeholder="Column Name">
				<input class="form-control" type="text" name="approval_status_fields_approve_value[]" placeholder="Column Value">
				<div class="input-group-append">
				    <button type="button" class="btn btn-success level_status_fields_approve_btn_add">+</button>
				    <button type="button" class="btn btn-danger level_status_fields_approve_btn_rem">-</button>
				</div>
			</div>			
		</div>
		<div class="level_status_fields_send_back_div">
			<div class="input-group level_status_fields_send_back_div_item mb-2">				
				<div class="input-group-prepend">
				    <span class="input-group-text">Send Back</span>
				</div>
				<input class="form-control" type="text" name="approval_status_fields_send_back_column[]" placeholder="Column Name">
				<input class="form-control" type="text" name="approval_status_fields_send_back_value[]" placeholder="Column Value">
				<div class="input-group-append">
				    <button type="button" class="btn btn-success level_status_fields_send_back_btn_add">+</button>
				    <button type="button" class="btn btn-danger level_status_fields_send_back_btn_rem">-</button>
				</div>
			</div>
		</div>
		<div class="level_status_fields_reject_div">
			<div class="input-group level_status_fields_reject_div_item mb-2">				
				<div class="input-group-prepend">
				    <span class="input-group-text">Reject</span>
				</div>
				<input class="form-control" type="text" name="approval_status_fields_reject_column[]" placeholder="Column Name">
				<input class="form-control" type="text" name="approval_status_fields_reject_value[]" placeholder="Column Value">
				<div class="input-group-append">
				    <button type="button" class="btn btn-success level_status_fields_reject_btn_add">+</button>
				    <button type="button" class="btn btn-danger level_status_fields_reject_btn_rem">-</button>
				</div>
			</div>
		</div>		
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_notifiable_namespace">Notification Class <span class="text-danger position-relative">*</span></label>
		<select class="form-control" name="approval_notifiable_namespace[]">
			<option value="0">None</option>
			@foreach($notifications as $notification_file)
			<option value="{{str_replace('.php','',$notification_file)}}">{{str_replace('.php','',namespaceBasePath($notification_file))}}</option>
			@endforeach
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_notifiable_params">Notification Channel</label>
		<input class="form-control" type="text" name="approval_notifiable_params[]" placeholder="Channel JSON ['mail','database']">
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_form">Approver<span class="text-danger position-relative">*</span></label>
		<select class="form-control select2" name="approval_user[APPROVE_USER_INDEX][]" multiple>
			@foreach($users as $user)
			<option value="{{$user->id}}">{{$user->name}}</option>
			@endforeach
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="form-group">
		<label for="approval_form">Form Required<span class="text-danger position-relative">*</span></label>
		<select class="form-control approval_form" name="approval_form[]">
			<option value="0">No</option>
			<option value="1">Yes</option>
		</select>
		<span class="d-none invalid-feedback"></span>
	</div>
	<div class="row form-group approval_form_div" style="display:none;">
		<div class="col-12">
			<label for="title">Form Update Fields<span class="text-danger position-relative">*</span></label>											
		</div>
		<div class="col-8">
			<select class="form-control approval_form_namespace">
				@foreach($models as $model_file)
				<option value="{{str_replace('.php','',$model_file)}}">{{str_replace('.php','',namespaceBasePath($model_file))}}</option>
				@endforeach
			</select>
		</div>
		<div class="col-4">
			<button type="button" class="btn btn-success float-right approval_form_add">Add</button>
		</div>
		<span class="d-none invalid-feedback"></span>
		<div class="col-12">
			<div class="row approval_form_div_item_div"></div>
		</div>
	</div>										
</div>