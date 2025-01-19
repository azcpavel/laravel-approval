@extends(config('approval-config.view-layout'))
@section(config('approval-config.view-section'))		
		<div class="flex-center position-ref full-height">            
			<div class="content">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-12 col-sm-8 col-md-6">
							<form class="form-horizontal" action="{{route(config('approval-config.route-name-prefix').'.update',['approval' => $approval->id])}}" method="post">
								@csrf
								@method('PUT')

								<div class="form-group">
									<label for="title">Title <span class="text-danger position-relative">*</span></label>
									<input class="form-control" type="text" name="title" placeholder="Title" id="title" value="{{old('title',$approval->title)}}" required>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group">
									<label for="approval_type">Approval Type<span class="text-danger position-relative">*</span></label>
									<select class="form-control" name="approval_type" id="approval_type">
										<option value="1" {{$approval->on_create == 1 ? 'selected' : ''}}>Create</option>
										<option value="2" {{$approval->on_update == 1 ? 'selected' : ''}}>Update</option>
										<option value="3" {{$approval->on_delete == 1 ? 'selected' : ''}}>Delete</option>
									</select>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group">
									<label for="do_swap">Enable Forward<span class="text-danger position-relative">*</span></label>
									<select class="form-control" name="do_swap" id="do_swap">
										<option value="0" {{$approval->do_swap == 0 ? 'selected' : ''}}>No</option>
										<option value="1" {{$approval->do_swap == 1 ? 'selected' : ''}}>Yes</option>
									</select>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group">
									<label for="model_namespace">Model <span class="text-danger position-relative">*</span></label>
									<select class="form-control" name="model_namespace">
										@foreach($models as $model_file)
										<?php
										$model_namespace = str_replace('.php','',$model_file);
										?>
										<option value="{{$model_namespace}}" {{($approval->approvable_type == $model_namespace) ? 'selected' : ''}}>{{str_replace('.php','',namespaceBasePath($model_file))}}</option>
										@endforeach
									</select>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group row">
									<div class="col-8">
										<label for="list_data_fields">List Data Fields<span class="text-danger position-relative">*</span></label>	
									</div>
									<div class="col-4 text-right">
										<button type="button" id="list_data_fields_btn_add" class="btn btn-success">Add</button>
									</div>
									<div class="col-12" id="list_data_fields_div">
										@foreach($approval->list_data_fields as $keyLDF => $valueLDF)
										<div class="row mt-2 list_data_fields_div_item">
											<div class="col-8">
												<input class="form-control" type="text" name="list_data_fields[]" placeholder="List Data Fields" value="{{$valueLDF}}" required>	
											</div>
											<div class="col-4 text-right">
												<button type="button" class="btn btn-danger list_data_fields_btn_remove">Remove</button>
											</div>
										</div>
										@endforeach
									</div>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group">
									<label for="properties">Properties</label>
									<input class="form-control" type="text" name="properties" placeholder="Properties" id="properties" value="{{$approval->properties}}">
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="row" id="model_delete_div" style="display:none;">
									<div class="col-12">
										<div class="form-group">
											<label for="do_delete">Data Delete<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="do_delete">
												<option value="0" {{(($approval->do_delete == 0) ? 'selected' : '')}}>No</option>
												<option value="1" {{(($approval->do_delete == 1) ? 'selected' : '')}}>Yes</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
									</div>
								</div>
								<div class="row" id="model_namespace_relation_div" style="display:none;">
									<div class="col-12">
										<div class="form-group">
											<label for="title">Update Slug <span class="text-danger position-relative">*</span></label>
											<input class="form-control" type="text" name="slug" placeholder="Slug" id="slug" value="{{$approval->slug}}">
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="row form-group">
											<div class="col-12">
											<label for="title">Update Model Fields<span class="text-danger position-relative">*</span></label>											
											</div>
											<div class="col-8">
												<select class="form-control" id="model_namespace_relation">
													@foreach($models as $model_file)
													<option value="{{str_replace('.php','',$model_file)}}">{{str_replace('.php','',namespaceBasePath($model_file))}}</option>
													@endforeach
												</select>
											</div>
											<div class="col-4">
												<button type="button" class="btn btn-success float-right" id="model_namespace_relation_add">Add</button>
											</div>
											<span class="d-none invalid-feedback"></span>
										</div>
									</div>
									<div class="w-100" id="model_namespace_relation_div_item">
										@foreach($approval->mappings as $keyM => $valueM)
										<?php
										$inputKey = namespaceBasePath($valueM->approvable_type);
										$model = new $valueM->approvable_type();
										$modelColumn = \DB::select('SHOW COLUMNS FROM '.$model->getTable());										
										?>
										<div class="col-12 mt-3 model_namespace_relation_div_item card p-3">
											<div class="w-100 float-left">
												<h5 class="float-left">{{$inputKey}}</h5>
												<button type="button" class="btn btn-danger btn-sm float-right" onclick="$(this).closest('.model_namespace_relation_div_item').remove();">Remove</button>
												<input type="hidden" name="model_namespace_relation_path[]" class="model_namespace_relation_path" value="{{$valueM->approvable_type}}">
												<input type="hidden" name="model_namespace_relation_key[]" class="model_namespace_relation_key" value="{{$inputKey}}">
												<input type="text" name="model_namespace_relation_title[]" class="float-left form-control model_namespace_relation_title" placeholder="Title" value="{{$valueM->title}}" required>
												<input type="text" name="model_relation_path[]" class="float-left my-3 form-control model_relation_path" placeholder="Relation" value="{{$valueM->relation}}">
											</div>
											<table class="table">
												<thead>
													<tr>
														<th>Column Name</th>
														<th>Display Option</th>
														<th>Option</th>
													</tr>
												</thead>
												<tbody class="model_namespace_relation_tbody">											
												@foreach($modelColumn as $keyMR => $valueMR)
													<?php
													$useColumn = $valueM->fields->where('field_name',$valueMR->Field)->first();													
													?>
													<tr>
														<td>{{$valueMR->Field}}</td>
														<td>
															<input type="text" name="model_namespace_relation_tbody_label[{{$inputKey}}][]" class="form-control model_namespace_relation_tbody_label" placeholder="Label" value="{{$useColumn->field_label??''}}">
															<input type="hidden" name="model_namespace_relation_tbody_name[{{$inputKey}}][]" class="form-control model_namespace_relation_tbody_name" value="{{$valueMR->Field}}" required>
															<input type="text" name="model_namespace_relation_tbody_relation[{{$inputKey}}][]" class="mt-2 form-control model_namespace_relation_tbody_relation mt-2" placeholder="Relation" value="{{$useColumn->field_relation??''}}">
															<div class="input-group">
																<input type="text" name="model_namespace_relation_tbody_relation_pk[{{$inputKey}}][]" class="mt-2 form-control model_namespace_relation_tbody_relation_pk mt-2" placeholder="Relation PK" value="{{$useColumn->field_relation_pk??''}}">
																<input type="text" name="model_namespace_relation_tbody_relation_show[{{$inputKey}}][]" class="mt-2 form-control model_namespace_relation_tbody_relation_show mt-2" placeholder="Relation Show" value="{{$useColumn->field_relation_show??''}}">
															</div>
															<select class="mt-2 form-control model_namespace_relation_tbody_type" name="model_namespace_relation_tbody_type[{{$inputKey}}][]" required>
																<option value="text" {{($useColumn->field_type??'') == 'text' ? 'selected' : ''}}>Text</option>
																<option value="number" {{($useColumn->field_type??'') == 'number' ? 'selected' : ''}}>Number</option>
																<option value="email" {{($useColumn->field_type??'') == 'email' ? 'selected' : ''}}>Email</option>
																<option value="textarea" {{($useColumn->field_type??'') == 'textarea' ? 'selected' : ''}}>Textarea</option>
																<option value="file" {{($useColumn->field_type??'') == 'file' ? 'selected' : ''}}>File</option>
																<option value="date" {{($useColumn->field_type??'') == 'date' ? 'selected' : ''}}>Date</option>
																<option value="select" {{($useColumn->field_type??'') == 'select' ? 'selected' : ''}}>Dropdown</option>
																<option value="select_single" {{($useColumnFF->mapped_field_type??'') == 'select_single' ? 'selected' : ''}}>Dropdown Single</option>
															</select>
														</td>
														<td><input type="checkbox" name="model_namespace_relation_tbody_check[{{$inputKey}}][]" class="model_namespace_relation_tbody_check" value="{{$keyMR}}" {{$useColumn ? 'checked' : ''}}></td>
													</tr>
												@endforeach
												</tdody>
											</table>
										</div>
										@endforeach
									</div>
								</div>
								<div class="form-group">
									<label for="view_route_name">View Route<span class="text-danger position-relative">*</span></label>
									<input class="form-control" type="text" name="view_route_name" placeholder="Route name" id="view_route" value="{{$approval->view_route_name}}" required>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group row">
									<div class="col-8">
										<label for="view_route_param">View Route Param<span class="text-danger position-relative">*</span></label>	
									</div>
									<div class="col-4 text-right">
										<button type="button" id="view_route_param_btn_add" class="btn btn-success">Add</button>
									</div>
									<div class="col-12" id="view_route_param_div">
										@foreach($approval->view_route_param as $keyR => $valueR)
										<div class="row mt-2 view_route_param_div_item">
											<div class="col-8 input-group">
												<input class="form-control" type="text" name="view_route_param_key[]" placeholder="Param Name" value="{{$keyR}}" required>	
												<input class="form-control" type="text" name="view_route_param_value[]" placeholder="Column Name" value="{{$valueR}}" required>	
											</div>
											<div class="col-4 text-right">
												<button type="button" class="btn btn-danger view_route_param_btn_remove">Remove</button>
											</div>
										</div>
										@endforeach										
									</div>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="row">
									<div class="col-12">
										<h4>
											Approval Level
											<button type="button" class="btn btn-success float-right" id="approval_level_add">Add</button>
										</h4>										
									</div>									
								</div>
								<div class="row" id="approval_level">
									@foreach($approval->levels as $keyAL => $valueAL)
									<div class="col-12 approval_level_item mt-3">
										<div class="form-group approval_level_no">
											<h5>Level <span>{{$keyAL+1}}</span> <button type="button" class="btn btn-danger btn-sm float-right approval_level_item_remove">Remove</button></h5>
											<hr>
										</div>
										<div class="form-group">
											<label for="approval_title">Title<span class="text-danger position-relative">*</span></label>
											<input class="form-control" type="text" name="approval_title[]" placeholder="Level Title" value="{{$valueAL->title}}" required>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_level">Level<span class="text-danger position-relative">*</span></label>
											<input class="form-control" type="number" min="1" max="100" name="approval_level[]" placeholder="Level" value="{{$valueAL->level}}" required>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_flex">Flexible<span class="text-danger position-relative">*</span></label>
											<input class="form-control" type="number" min="0" max="100" name="approval_flex[]" placeholder="Flexible" value="{{$valueAL->is_flexible}}" required>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_group_notification">Group Notification<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_group_notification[]">
												<option value="0" {{(($valueAL->group_notification == 0) ? 'selected' : '')}}>No</option>
												<option value="1" {{(($valueAL->group_notification == 1) ? 'selected' : '')}}>Yes</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_next_notification">Next Level Notification<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_next_notification[]">
												<option value="1" {{(($valueAL->next_level_notification == 1) ? 'selected' : '')}}>Yes</option>
												<option value="0" {{(($valueAL->next_level_notification == 0) ? 'selected' : '')}}>No</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_next_user">Next Level User Selection<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_next_user[]">
												<option value="0" {{(($valueAL->next_level_user == 0) ? 'selected' : '')}}>No</option>
												<option value="1" {{(($valueAL->next_level_user == 1) ? 'selected' : '')}}>Yes</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_need_attachment">Need Attachment<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_need_attachment[]">
												<option value="1" {{(($valueAL->need_attachment == 1) ? 'selected' : '')}}>Yes</option>
												<option value="0" {{(($valueAL->need_attachment == 0) ? 'selected' : '')}}>No</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>										
										<div class="form-group">
											<label for="approval_approve_reason">Approve Reason Mandatory<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_approve_reason[]">
												<option value="0" {{(($valueAL->is_approve_reason_required == 0) ? 'selected' : '')}}>No</option>
												<option value="1" {{(($valueAL->is_approve_reason_required == 1) ? 'selected' : '')}}>Yes</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_reject_reason">Reject Reason Mandatory<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_reject_reason[]">
												<option value="1" {{(($valueAL->is_reject_reason_required == 1) ? 'selected' : '')}}>Yes</option>
												<option value="0" {{(($valueAL->is_reject_reason_required == 0) ? 'selected' : '')}}>No</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_data_mapped">Data Mapped<span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_data_mapped[]">
												<option value="1" {{(($valueAL->is_data_mapped == 1) ? 'selected' : '')}}>Yes</option>
												<option value="0" {{(($valueAL->is_data_mapped == 0) ? 'selected' : '')}}>No</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_action_type">Action Type<span class="text-danger position-relative">*</span></label>
											<select class="form-control approval_action_type" name="approval_action_type[]">												
												<option value="0">None</option>
												<option value="1" {{(($valueAL->action_type == 1) ? 'selected' : '')}}>Class Path</option>
												<option value="2" {{(($valueAL->action_type == 2) ? 'selected' : '')}}>Redirect URL</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_action_data" class="action-class">Action Class Data</label>		
											<label for="approval_action_data" class="action-url">Action URL Data</label>		
											<div class="input-group action-class">
												<input class="form-control" type="text" name="approval_action_class_before_path[]" placeholder="Before Namespace" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'before') ? $valueAL->action_data->before->class??'' : '' }}">
												<input class="form-control" type="text" name="approval_action_class_before_method[]" placeholder="Before Method" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'before') ? $valueAL->action_data->before->method??'' : '' }}">
											</div>
											<div class="input-group action-class mt-2">
												<input class="form-control" type="text" name="approval_action_class_approve_path[]" placeholder="Approve Namespace" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'approve') ? $valueAL->action_data->approve->class??'' : '' }}">
												<input class="form-control" type="text" name="approval_action_class_approve_method[]" placeholder="Approve Method" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'approve') ? $valueAL->action_data->approve->method??'' : '' }}">
											</div>
											<div class="input-group action-class mt-2">
												<input class="form-control" type="text" name="approval_action_class_send_back_path[]" placeholder="Reject Namespace" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'send_back') ? $valueAL->action_data->send_back->class??'' : '' }}">
												<input class="form-control" type="text" name="approval_action_class_send_back_method[]" placeholder="Reject Method" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'send_back') ? $valueAL->action_data->send_back->method??'' : '' }}">
											</div>
											<div class="input-group action-class mt-2">
												<input class="form-control" type="text" name="approval_action_class_reject_path[]" placeholder="Reject Namespace" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'reject') ? $valueAL->action_data->reject->class??'' : '' }}">
												<input class="form-control" type="text" name="approval_action_class_reject_method[]" placeholder="Reject Method" value="{{$valueAL->action_type == 1 && $valueAL->action_data &&property_exists($valueAL->action_data,'reject') ? $valueAL->action_data->reject->method??'' : '' }}">
											</div>		
											<div class="input-group action-url mt-2">
												<input class="form-control" type="text" name="approval_action_url_approve_route[]" placeholder="Approve Route Name" value="{{$valueAL->action_type == 2 && $valueAL->action_data &&property_exists($valueAL->action_data,'approve') ? $valueAL->action_data->approve->route??'' : '' }}">
												<input class="form-control" type="text" name="approval_action_url_approve_param[]" placeholder="JSON {'param_name':'main_model_column'}" value="{{$valueAL->action_type == 2 && $valueAL->action_data &&property_exists($valueAL->action_data,'approve') ? json_encode($valueAL->action_data->approve->param)??'' : '' }}">
											</div>
											<div class="input-group action-url mt-2">
												<input class="form-control" type="text" name="approval_action_url_reject_route[]" placeholder="Reject Route Name" value="{{$valueAL->action_type == 2 && $valueAL->action_data &&property_exists($valueAL->action_data,'reject') ? $valueAL->action_data->reject->route??'' : '' }}">
												<input class="form-control" type="text" name="approval_action_url_reject_param[]" placeholder="JSON {'param_name':'main_model_column'}" value="{{$valueAL->action_type == 2 && $valueAL->action_data &&property_exists($valueAL->action_data,'reject') ? json_encode($valueAL->action_data->reject->param)??'' : '' }}">
											</div>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_action_frequency">Action Frequency</label>
											<select class="form-control" name="approval_action_frequency[]">												
												<option value="0">None</option>
												<option value="1" {{$valueAL->action_frequency == 1 ? 'selected' : ''}}>Every Time</option>
												<option value="2" {{$valueAL->action_frequency == 2 ? 'selected' : ''}}>Final</option>
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_status_fields">Status Fields</label>
											<div class="level_status_fields_approve_div">
												@if($valueAL->status_fields && property_exists($valueAL->status_fields, 'approve'))
												@foreach($valueAL->status_fields->approve as $keyALSF => $valueALSF)
												<div class="input-group level_status_fields_approve_div_item mb-2">				
													<div class="input-group-prepend">
													    <span class="input-group-text">Approve</span>
													</div>
													<input class="form-control" type="text" name="approval_status_fields_approve_column[]" placeholder="Column Name" value="{{$keyALSF}}">
													<input class="form-control" type="text" name="approval_status_fields_approve_value[]" placeholder="Column Value" value="{{$valueALSF}}">
													<div class="input-group-append">
													    <button type="button" class="btn btn-success level_status_fields_approve_btn_add">+</button>
													    <button type="button" class="btn btn-danger level_status_fields_approve_btn_rem">-</button>
													</div>
												</div>
												@endforeach
												@else
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
												@endif
											</div>
											<div class="level_status_fields_send_back_div">
												@if($valueAL->status_fields && property_exists($valueAL->status_fields, 'send_back'))
												@foreach($valueAL->status_fields->send_back as $keyALSF => $valueALSF)
												<div class="input-group level_status_fields_send_back_div_item mb-2">				
													<div class="input-group-prepend">
													    <span class="input-group-text">Approve</span>
													</div>
													<input class="form-control" type="text" name="approval_status_fields_send_back_column[]" placeholder="Column Name" value="{{$keyALSF}}">
													<input class="form-control" type="text" name="approval_status_fields_send_back_value[]" placeholder="Column Value" value="{{$valueALSF}}">
													<div class="input-group-append">
													    <button type="button" class="btn btn-success level_status_fields_send_back_btn_add">+</button>
													    <button type="button" class="btn btn-danger level_status_fields_send_back_btn_rem">-</button>
													</div>
												</div>
												@endforeach
												@else
												<div class="input-group level_status_fields_send_back_div_item mb-2">				
													<div class="input-group-prepend">
													    <span class="input-group-text">Approve</span>
													</div>
													<input class="form-control" type="text" name="approval_status_fields_send_back_column[]" placeholder="Column Name">
													<input class="form-control" type="text" name="approval_status_fields_send_back_value[]" placeholder="Column Value">
													<div class="input-group-append">
													    <button type="button" class="btn btn-success level_status_fields_send_back_btn_add">+</button>
													    <button type="button" class="btn btn-danger level_status_fields_send_back_btn_rem">-</button>
													</div>
												</div>
												@endif
											</div>
											<div class="level_status_fields_reject_div">
												@if($valueAL->status_fields && property_exists($valueAL->status_fields, 'reject'))
												@foreach($valueAL->status_fields->reject as $keyALSF => $valueALSF)
												<div class="input-group level_status_fields_reject_div_item mb-2">				
													<div class="input-group-prepend">
													    <span class="input-group-text">Reject</span>
													</div>
													<input class="form-control" type="text" name="approval_status_fields_reject_column[]" placeholder="Column Name" value="{{$keyALSF}}">
													<input class="form-control" type="text" name="approval_status_fields_reject_value[]" placeholder="Column Value" value="{{$valueALSF}}">
													<div class="input-group-append">
													    <button type="button" class="btn btn-success level_status_fields_reject_btn_add">+</button>
													    <button type="button" class="btn btn-danger level_status_fields_reject_btn_rem">-</button>
													</div>
												</div>
												@endforeach
												@else
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
												@endif
											</div>		
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_notifiable_namespace">Notification Class <span class="text-danger position-relative">*</span></label>
											<select class="form-control" name="approval_notifiable_namespace[]">
												<option value="0">None</option>
												@foreach($notifications as $notification_file)
												<?php
												$notification_namespace = str_replace('.php','',$notification_file);
												?>
												<option value="{{$notification_namespace}}" {{$notification_namespace == $valueAL->notifiable_class ? 'selected' : ''}}>{{str_replace('.php','',namespaceBasePath($notification_file))}}</option>
												@endforeach
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_notifiable_params">Notification Channel</label>
											<input class="form-control" type="text" name="approval_notifiable_params[]" placeholder="Channel JSON ['mail','database']" value="{{($valueAL->notifiable_params && property_exists($valueAL->notifiable_params,'channels')) ? json_encode($valueAL->notifiable_params->channels) : ''}}">
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_form">Approver<span class="text-danger position-relative">*</span></label>
											<select class="form-control select2" name="approval_user[APPROVE_USER_INDEX][]" multiple>
												<?php
												$levelUsers = $valueAL->users->pluck('id')->toArray();
												?>
												@foreach($users as $user)
												<option value="{{$user->id}}" {{in_array($user->id,$levelUsers) ? 'selected' : ''}}>{{$user->name}}</option>
												@endforeach
											</select>
											<span class="d-none invalid-feedback"></span>
										</div>
										<div class="form-group">
											<label for="approval_form">Form Required<span class="text-danger position-relative">*</span></label>
											<select class="form-control approval_form" name="approval_form[]">
												<option value="0">No</option>
												<option value="1" {{$valueAL->is_form_required == 1 ? 'selected' : ''}}>Yes</option>
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
												<div class="row approval_form_div_item_div">
													@foreach($valueAL->forms as $keyALF => $valueALF)
													<?php
													$inputKeyFF = namespaceBasePath($valueALF->approvable_type);
													$modelFF = new $valueALF->approvable_type();
													$modelFFColumn = \DB::select('SHOW COLUMNS FROM '.$modelFF->getTable());										
													?>
													<div class="col-12 mt-3 approval_form_div_item_div_item card p-3">
														<div class="w-100 float-left">
															<h5 class="float-left">{{$inputKeyFF}}</h5>
															<button type="button" class="btn btn-danger btn-sm float-right approval_form_div_item_div_item_remove">Remove</button>
															<input type="hidden" name="approval_form_path[]" class="approval_form_path" value="{{$valueALF->approvable_type}}">
															<input type="hidden" name="approval_form_key[]" class="approval_form_key" value="{{$inputKeyFF}}">
															<input type="text" name="approval_form_title[]" class="float-left form-control approval_form_title" placeholder="Title" value="{{$valueALF->title}}" required>
															<input type="text" name="approval_form_relation[]" class="float-left my-3 form-control approval_form_relation" placeholder="Relation" value="{{$valueALF->relation}}">
														</div>
														<table class="table">
															<thead>
																<tr>
																	<th>Column Name</th>
																	<th>Display Option</th>
																	<th>Option</th>
																</tr>
															</thead>
															<tbody class="approval_form_tbody">											
															@foreach($modelFFColumn as $keyALFF => $valueALFF)
																<?php
																$useColumnFF = $valueALF->form_data->where('mapped_field_name',$valueALFF->Field)->first();													
																?>
																<tr>
																	<td>{{$valueALFF->Field}}</td>
																	<td>
																		<input type="text" name="approval_form_tbody_label[{{$keyAL}}][{{$inputKeyFF}}][]" class="form-control approval_form_tbody_label" placeholder="Label" value="{{$useColumnFF->mapped_field_label??''}}">																		
																		<input type="text" name="approval_form_tbody_relation[{{$keyAL}}][{{$inputKeyFF}}][]" class="mt-2 form-control approval_form_tbody_relation mt-2" placeholder="Relation" value="{{$useColumnFF->mapped_field_relation??''}}">
																		<div class="input-group">
																			<input type="text" name="approval_form_tbody_relation_pk[{{$keyAL}}][{{$inputKeyFF}}][]" class="mt-2 form-control approval_form_tbody_relation_pk mt-2" placeholder="Relation PK" value="{{$useColumnFF->mapped_field_relation_pk??''}}">
																			<input type="text" name="approval_form_tbody_relation_show[{{$keyAL}}][{{$inputKeyFF}}][]" class="mt-2 form-control approval_form_tbody_relation_show mt-2" placeholder="Relation Show" value="{{$useColumnFF->mapped_field_relation_show??''}}">
																		</div>
																		<select class="mt-2 form-control approval_form_tbody_type" name="approval_form_tbody_type[{{$keyAL}}][{{$inputKeyFF}}][]" required>
																			<option value="text" {{($useColumnFF->mapped_field_type??'') == 'text' ? 'selected' : ''}}>Text</option>
																			<option value="number" {{($useColumnFF->mapped_field_type??'') == 'number' ? 'selected' : ''}}>Number</option>
																			<option value="email" {{($useColumnFF->mapped_field_type??'') == 'email' ? 'selected' : ''}}>Email</option>
																			<option value="textarea" {{($useColumnFF->mapped_field_type??'') == 'textarea' ? 'selected' : ''}}>Textarea</option>
																			<option value="file" {{($useColumnFF->mapped_field_type??'') == 'file' ? 'selected' : ''}}>File</option>
																			<option value="date" {{($useColumnFF->mapped_field_type??'') == 'date' ? 'selected' : ''}}>Date</option>
																			<option value="select" {{($useColumnFF->mapped_field_type??'') == 'select' ? 'selected' : ''}}>Dropdown</option>
																		</select>
																		<input type="hidden" name="approval_form_tbody_name[{{$keyAL}}][{{$inputKeyFF}}][]" class="form-control approval_form_tbody_name" value="{{$valueALFF->Field}}" required>
																	</td>
																	<td><input type="checkbox" name="approval_form_tbody_check[{{$keyAL}}][{{$inputKeyFF}}][]" class="approval_form_tbody_check" value="{{$keyALFF}}" {{$useColumnFF ? 'checked' : ''}}></td>
																</tr>
															@endforeach
															</tdody>
														</table>
													</div>
													@endforeach
												</div>
											</div>
										</div>										
									</div>
									@endforeach						
								</div>
								<div class="form-group">
									<button type="submit" class="btn btn-info">Save</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<style type="text/css">
			.action-class, .action-url{				
				width: 100%;
			}
		</style>
@endsection
@push('script')
@if(config('approval-config.load-script'))
@include('laravel-approval::partials.script')
@endif
<script type="text/javascript">
	$(window).on("load",function(){
		$('#approval_type, .approval_action_type, .approval_form').trigger('change');
		updateFormIndex();
	});

	$(document).on("change","#approval_type",function(){
		var $input = $(this);
		$('#model_namespace_relation_div, #model_delete_div').hide();
		$('#slug').removeAttr('required');
		$('#do_delete').removeAttr('required');

		if($input.val() == 2){
			$('#model_namespace_relation_div').show();
			$('#slug').prop('required',true);
		}else if($input.val() == 3){
			$('#model_delete_div').show();
			$('#do_delete').prop('required',true);
		}
	});

	$(document).on("change",".approval_action_type",function(){
		var $input = $(this);
		var $parent = $(this).closest('.approval_level_item');

		$parent.find('.action-class, .action-url').hide();
		if($input.val() == 1){
			$parent.find('.action-class').show();
		}else if($input.val() == 2){			
			$parent.find('.action-url').show();
		}
	});

	$(document).on("click",".model_namespace_relation_tbody_check",function(){
		var $input = $(this);
		if($input.prop("checked")){
			$input.closest('tr').find('.model_namespace_relation_tbody_label').removeAttr("required");
			$input.closest('tr').find('.model_namespace_relation_tbody_label').attr({"required":true});
		}else{			
			$input.closest('tr').find('.model_namespace_relation_tbody_label').removeAttr("required");
		}
	});

	$(document).on("click",".list_data_fields_btn_remove",function(){
		var $input = $(this);
		if($('#list_data_fields_div').children().length > 1){
			$input.parent().parent().remove();
		}
	});

	$(document).on("click","#list_data_fields_btn_add",function(){
		var $input = $(this);
		$('#list_data_fields_div').append(
			'<div class="row mt-2 list_data_fields_div_item">'+
				'<div class="col-8">'+
					'<input class="form-control" type="text" name="list_data_fields[]" placeholder="List Data Fields" id="list_data_fields" required>	'+
				'</div>'+
				'<div class="col-4 text-right">'+
					'<button type="button" class="btn btn-danger list_data_fields_btn_remove">Remove</button>'+
				'</div>'+
			'</div>'
		);
	});

	$(document).on("click",".view_route_param_btn_remove",function(){
		var $input = $(this);
		if($('#view_route_param_div').children().length > 1){
			$input.parent().parent().remove();
		}
	});

	$(document).on("click","#view_route_param_btn_add",function(){
		var $input = $(this);
		$('#view_route_param_div').append(
			'<div class="row mt-2 view_route_param_div_item">'+
				'<div class="col-8 input-group">'+
					'<input class="form-control" type="text" name="view_route_param_key[]" placeholder="Param Name" required>	'+
					'<input class="form-control" type="text" name="view_route_param_value[]" placeholder="Column Name" required>	'+
				'</div>'+
				'<div class="col-4 text-right">'+
					'<button type="button" class="btn btn-danger view_route_param_btn_remove">Remove</button>'+
				'</div>'+
			'</div>'
		);
	});

	$(document).on("click","#model_namespace_relation_add",function(){
		var $input = $(this);
		var inputKey = $('#model_namespace_relation option:selected').text();
		var inputVal = $('#model_namespace_relation').val();
		$htmlWrap = $('<div class="col-12 mt-3 model_namespace_relation_div_item card p-3">'+
			'<div class="w-100 float-left">'+
				'<h5 class="float-left">'+inputKey+'</h5>'+
				'<button type="button" class="btn btn-danger btn-sm float-right" onclick="$(this).closest(\'.model_namespace_relation_div_item\').remove();">Remove</button>'+
				'<input type="hidden" name="model_namespace_relation_path[]" class="model_namespace_relation_path" value="'+inputVal+'">'+
				'<input type="hidden" name="model_namespace_relation_key[]" class="model_namespace_relation_key" value="'+inputKey+'">'+
				'<input type="text" name="model_namespace_relation_title[]" class="float-left form-control model_namespace_relation_title" placeholder="Title" required>'+
				'<input type="text" name="model_relation_path[]" class="float-left my-3 form-control model_relation_path" placeholder="Relation">'+
			'</div>'+
		'</div>');
		$.ajax({
			'url' : '{{route(config('approval-config.route-name-prefix').'.model_info')}}?model_namespace='+inputVal,
			'type' : 'GET',
			'dataType' : 'JSON'
		}).done(function(response){
			$htmlWrap.append('<table class="table">'+
					'<thead>'+
					'<tr>'+
						'<th>Column Name</th>'+
						'<th>Display Option</th>'+
						'<th>Option</th>'+
					'</tr>'+
					'</thead>'+
					'<tbody id="model_namespace_relation_tbody"></tdody>'+
				'</table>');
			$.each(response,function(indKey, val){
				$htmlWrap.find('#model_namespace_relation_tbody').append(
					'<tr>'+
						'<td>'+val.Field+'</td>'+
						'<td><input type="text" name="model_namespace_relation_tbody_label['+inputKey+'][]" class="form-control model_namespace_relation_tbody_label" placeholder="Label">'+
						'<input type="hidden" name="model_namespace_relation_tbody_name['+inputKey+'][]" class="form-control model_namespace_relation_tbody_name" value="'+val.Field+'" required>'+
						'<input type="text" name="model_namespace_relation_tbody_relation['+inputKey+'][]" class="mt-2 form-control model_namespace_relation_tbody_relation mt-2" placeholder="Relation">'+
						'<div class="input-group">'+						
						'<input type="text" name="model_namespace_relation_tbody_relation_pk['+inputKey+'][]" class="mt-2 form-control model_namespace_relation_tbody_relation_pk mt-2" placeholder="Relation PK">'+
						'<input type="text" name="model_namespace_relation_tbody_relation_show['+inputKey+'][]" class="mt-2 form-control model_namespace_relation_tbody_relation_show mt-2" placeholder="Relation Show">'+
						'</div>'+
						'<select class="mt-2 form-control model_namespace_relation_tbody_type" name="model_namespace_relation_tbody_type['+inputKey+'][]" required><option value="text">Text</option><option value="number">Number</option><option value="email">Email</option><option value="textarea">Textarea</option><option value="file">File</option><option value="date">Date</option><option value="select">Dropdown</option></select></td>'+
						'<td><input type="checkbox" name="model_namespace_relation_tbody_check['+inputKey+'][]" class="model_namespace_relation_tbody_check" value="'+indKey+'"></td>'+
					'</tr>'
				);
			});
			$('#model_namespace_relation_div_item').append($htmlWrap);
		});		
	});

	$(document).on("click","#approval_level_add",function(){
		$.ajax({
			url : '{{route(config('approval-config.route-name-prefix').'.model_level_form')}}',
			'type' : 'GET',
			'dataType' : 'html'
		}).done(function(response){
			var $htmlData = $(response.replace('APPROVAL_LEVEL',$('.approval_level_item').length + 1).replace('APPROVE_USER_INDEX',$('.approval_level_item').length));
			$('#approval_level').append($htmlData);
			$htmlData.find('.select2').select2();
			$htmlData.find('.action-class, .action-url').hide();
			updateFormIndex();
		});
		
	});

	$(document).on("click",".level_status_fields_approve_btn_add",function(){
		var $item = $(this);
		$item.closest('.level_status_fields_approve_div').append(
			'<div class="input-group level_status_fields_approve_div_item mb-2">'+
				'<div class="input-group-prepend">'+
				    '<span class="input-group-text">Approve</span>'+
				'</div>'+
				'<input class="form-control" type="text" name="approval_status_fields_approve_column[]" placeholder="Column Name">'+
				'<input class="form-control" type="text" name="approval_status_fields_approve_value[]" placeholder="Column Value">'+
				'<div class="input-group-append">'+
				    '<button type="button" class="btn btn-success level_status_fields_approve_btn_add">+</button>'+
				    '<button type="button" class="btn btn-danger level_status_fields_approve_btn_rem">-</button>'+
				'</div>'+
			'</div>'
		);
		updateFormIndex();
	});

	$(document).on("click",".level_status_fields_approve_btn_rem",function(){
		var $item = $(this);
		if($item.closest('.level_status_fields_approve_div').children().length > 1){
			$item.closest('.level_status_fields_approve_div_item').remove();
			updateFormIndex();
		}
	});

	$(document).on("click",".level_status_fields_send_back_btn_add",function(){
		var $item = $(this);
		$item.closest('.level_status_fields_send_back_div').append(
			'<div class="input-group level_status_fields_send_back_div_item mb-2">'+
				'<div class="input-group-prepend">'+
				    '<span class="input-group-text">Approve</span>'+
				'</div>'+
				'<input class="form-control" type="text" name="approval_status_fields_send_back_column[]" placeholder="Column Name">'+
				'<input class="form-control" type="text" name="approval_status_fields_send_back_value[]" placeholder="Column Value">'+
				'<div class="input-group-append">'+
				    '<button type="button" class="btn btn-success level_status_fields_send_back_btn_add">+</button>'+
				    '<button type="button" class="btn btn-danger level_status_fields_send_back_btn_rem">-</button>'+
				'</div>'+
			'</div>'
		);
		updateFormIndex();
	});

	$(document).on("click",".level_status_fields_send_back_btn_rem",function(){
		var $item = $(this);
		if($item.closest('.level_status_fields_send_back_div').children().length > 1){
			$item.closest('.level_status_fields_send_back_div_item').remove();
			updateFormIndex();
		}
	});

	$(document).on("click",".level_status_fields_reject_btn_add",function(){
		var $item = $(this);
		$item.closest('.level_status_fields_reject_div').append(
			'<div class="input-group level_status_fields_reject_div_item mb-2">'+
				'<div class="input-group-prepend">'+
				    '<span class="input-group-text">Reject</span>'+
				'</div>'+
				'<input class="form-control" type="text" name="approval_status_fields_reject_column[]" placeholder="Column Name">'+
				'<input class="form-control" type="text" name="approval_status_fields_reject_value[]" placeholder="Column Value">'+
				'<div class="input-group-append">'+
				    '<button type="button" class="btn btn-success level_status_fields_reject_btn_add">+</button>'+
				    '<button type="button" class="btn btn-danger level_status_fields_reject_btn_rem">-</button>'+
				'</div>'+
			'</div>'
		);
		updateFormIndex();
	});

	$(document).on("click",".level_status_fields_reject_btn_rem",function(){
		var $item = $(this);
		if($item.closest('.level_status_fields_reject_div').children().length > 1){
			$item.closest('.level_status_fields_reject_div_item').remove();
			updateFormIndex();
		}
	});

	
	$(document).on("change",".approval_form",function(){
		var $input = $(this);
		if($input.val() == 1)
			$input.closest('.approval_level_item').find('.approval_form_div').show();
		else
			$input.closest('.approval_level_item').find('.approval_form_div').hide();
	});

	$(document).on("click",".approval_form_add",function(){	
		var $input = $(this);
		var $wrapParent = $input.closest('.approval_level_item');
		var $wrap = $input.closest('.approval_form_div');
		var approvalLevel = $wrapParent.index();	
		var inputKey = $wrap.find('.approval_form_namespace option:selected').text();
		var inputVal = $wrap.find('.approval_form_namespace').val();
		$htmlWrap = $('<div class="col-12 mt-3 approval_form_div_item_div_item card p-3">'+
			'<div class="w-100 float-left">'+
				'<h5 class="float-left">'+inputKey+'</h5>'+
				'<button type="button" class="btn btn-danger btn-sm float-right approval_form_div_item_div_item_remove" >Remove</button>'+
				'<input type="hidden" name="approval_form_path['+approvalLevel+'][]" class="approval_form_path" value="'+inputVal+'">'+
				'<input type="hidden" name="approval_form_key['+approvalLevel+'][]" class="approval_form_key" value="'+inputKey+'">'+
				'<input type="text" name="approval_form_title['+approvalLevel+'][]" class="float-left mt-3 form-control approval_form_title" placeholder="Title" required>'+
				'<input type="text" name="approval_form_relation['+approvalLevel+'][]" class="float-left my-3 form-control approval_form_relation" placeholder="Relation">'+
			'</div>');
		$.ajax({
			'url' : '{{route(config('approval-config.route-name-prefix').'.model_info')}}?model_namespace='+inputVal,
			'type' : 'GET',
			'dataType' : 'JSON'
		}).done(function(response){
			$htmlWrap.append('<table class="table">'+
					'<thead>'+
					'<tr>'+
						'<th>Column Name</th>'+
						'<th>Display Option</th>'+
						'<th>Option</th>'+
					'</tr>'+
					'</thead>'+
					'<tbody class="approval_form_tbody"></tdody>'+
				'</table>');
			$.each(response,function(indKey, val){
				$htmlWrap.find('.approval_form_tbody').append(
					'<tr>'+
						'<td>'+val.Field+'</td>'+
						'<td><input type="text" name="approval_form_tbody_label['+approvalLevel+']['+inputKey+'][]" class="form-control approval_form_tbody_label" placeholder="Label">'+
						'<input type="text" name="approval_form_tbody_relation['+approvalLevel+']['+inputKey+'][]" class="mt-2 form-control approval_form_tbody_relation" placeholder="Relation">'+
						'<div class="input-group">'+						
						'<input type="text" name="approval_form_tbody_relation_pk['+approvalLevel+']['+inputKey+'][]" class="mt-2 form-control approval_form_tbody_relation_pk mt-2" placeholder="Relation PK">'+
						'<input type="text" name="approval_form_tbody_relation_show['+approvalLevel+']['+inputKey+'][]" class="mt-2 form-control approval_form_tbody_relation_show mt-2" placeholder="Relation Show">'+
						'</div>'+
						'<select class="mt-2 form-control approval_form_tbody_type" name="approval_form_tbody_type['+approvalLevel+']['+inputKey+'][]" required><option value="text">Text</option><option value="number">Number</option><option value="email">Email</option><option value="textarea">Textarea</option><option value="file">File</option><option value="date">Date</option><option value="select">Dropdown</option></select>'+
						'<input type="hidden" name="approval_form_tbody_name['+approvalLevel+']['+inputKey+'][]" class="form-control approval_form_tbody_name" value="'+val.Field+'" required></td>'+
						'<td><input type="checkbox" name="approval_form_tbody_check['+approvalLevel+']['+inputKey+'][]" class="approval_form_tbody_check" value="'+indKey+'"></td>'+
					'</tr>'
				);
			});
			$wrap.find('.approval_form_div_item_div').append($htmlWrap);
		});		
	});

	$(document).on("click",".approval_form_tbody_check",function(){
		var $input = $(this);
		if($input.prop("checked")){
			$input.closest('tr').find('.approval_form_tbody_label').removeAttr("required");
			$input.closest('tr').find('.approval_form_tbody_label').attr({"required":true});
		}else{			
			$input.closest('tr').find('.approval_form_tbody_label').removeAttr("required");
		}
	});

	$(document).on("click",".approval_level_item_remove",function(){
		var $input = $(this);
		$input.closest('.approval_level_item').remove();
		updateFormIndex();
	});

	$(document).on("click",".approval_form_div_item_div_item_remove",function(){
		var $input = $(this);
		$input.closest('.approval_form_div_item_div_item').remove();
		updateFormIndex();
	});

	function updateFormIndex() {
		$.each($('.approval_level_item'),function(indForm, elForm){
			var $item = $(elForm);
			$item.find('.approval_level_no > h5 > span').html(indForm+1);
			$item.find(':input[name^=approval_user]').attr('name','approval_user['+(indForm)+'][]');

			$item.find(':input[name^=approval_status_fields_approve_column]').attr('name','approval_status_fields_approve_column['+(indForm)+'][]');
			$item.find(':input[name^=approval_status_fields_approve_value]').attr('name','approval_status_fields_approve_value['+(indForm)+'][]');
			
			$item.find(':input[name^=approval_status_fields_send_back_column]').attr('name','approval_status_fields_send_back_column['+(indForm)+'][]');
			$item.find(':input[name^=approval_status_fields_send_back_value]').attr('name','approval_status_fields_send_back_value['+(indForm)+'][]');

			$item.find(':input[name^=approval_status_fields_reject_column]').attr('name','approval_status_fields_reject_column['+(indForm)+'][]');
			$item.find(':input[name^=approval_status_fields_reject_value]').attr('name','approval_status_fields_reject_value['+(indForm)+'][]');

			$item.find(':input[name^=approval_form_path]').attr('name','approval_form_path['+(indForm)+'][]');
			$item.find(':input[name^=approval_form_key]').attr('name','approval_form_key['+(indForm)+'][]');
			$item.find(':input[name^=approval_form_title]').attr('name','approval_form_title['+(indForm)+'][]');
			$item.find(':input[name^=approval_form_relation]').attr('name','approval_form_relation['+(indForm)+'][]');

			$item.find(':input[name^=approval_form_tbody_label]').each(function(indAFD, elAFD){				
				var keyEL = $(elAFD).attr('name').match(/\[[a-zA-Z]+\]/)[0];
				$(elAFD).attr('name','approval_form_tbody_label['+(indForm)+']'+keyEL+'[]');
			});

		});
	}
</script>
@endpush