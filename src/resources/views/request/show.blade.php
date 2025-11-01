@extends(config('approval-config.view-layout'))
@section(config('approval-config.view-section'))
<?php
function get_approval_type($approval){
    if($approval->is_approved)
        return 'Approved';
    else if($approval->is_swaped)
        return 'Forwarded';
    else if($approval->is_resubmitted)
        return 'Re-Submitted';
    else if($approval->is_rejected)
        return 'Rejected';
    else if($approval->is_commented)
        return 'Commented';
    else if($approval->is_send_back)
        return 'Send Back';
}
?>
		<div class="flex-center position-ref full-height">            
			<div class="content">
				<div class="container">
					<div class="row justify-content-center">
						@if(session()->has('msg_type'))
						<div class="alert alert-{{session('msg_type')}} alert-dismissible fade show" role="alert">
							{{session('msg_data')}}
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						@endif
						<div class="col-12">
							<h5>{{$approvalRequest->approval->title}}</h5>
						</div>
						<div class="col-12 col-md-4 mt-4 top-card">
							<div class="card">
								<div class="card-header">
									Requested By
								</div>
								<div class="card-body">									
									@foreach($approvalRequest->approval->list_data_fields as $keyFN => $valueFN)
									<?php
									if(strpos($valueFN,":") !== false){
										$relationItem = explode(":", $valueFN);
										$relationItemModel = $relationItem[0];
										$relationItemModelField = $relationItem[1];
										if($approvalRequest->approvable->$relationItemModel instanceof Illuminate\Database\Eloquent\Collection){
		                                    foreach($approvalRequest->approvable->$relationItemModel as $keyRMIR => $valueRMIR){
		                                        echo $valueRMIR->$relationItemModelField.'</br>';
		                                    }
		                                }
		                                else{
		                                    echo $approvalRequest->approvable->$relationItemModel->$relationItemModelField.'</br>';
		                                }
									}else
										echo $approvalRequest->approvable->$valueFN.'<br>';
									?>
									@endforeach
									{{approvalDate($approvalRequest->created_at, true)}}<br>
									@php
		                            $route_params = array_values($approvalRequest->approval->view_route_param)[0];
		                            $param_value = null;
		                            if(strpos($route_params,'.') !== false){
		                                $params = explode('.',$route_params);
		                                foreach($params as $keyParam => $param){
		                                    if($keyParam == 0)
		                                        $param_value = $approvalRequest->approvable->$param;
		                                    else
		                                        $param_value = $param_value->$param;
		                                }
		                            }else
		                                $param_value = $approvalRequest->approvable[$route_params];
		                            @endphp
									<a target="_blank" href="{{route($approvalRequest->approval->view_route_name,[array_keys($approvalRequest->approval->view_route_param)[0]=>$param_value])}}">View Details</a>
								</div>
							</div>							
						</div>
						<div class="col-12 col-md-4 mt-4 top-card">
							<div class="card">
								<div class="card-header">
									Approval Information
								</div>
								<div class="card-body">									
									Total Level: {{$approvalRequest->approval->levels->count()}}<br>
									Total Submission: {{$approvalRequest->approvers->count()}}<br>
									No. of Approve: {{$approvalRequest->approvers->count() > 0 ? $approvalRequest->approvers->where('is_approved',1)->count() : 0}}<br>
									No. of Rejects: {{$approvalRequest->approvers->count() > 0 ? $approvalRequest->approvers->where('is_rejected',1)->count() : 0}}
								</div>
							</div>							
						</div>
						<div class="col-12 col-md-4 mt-4 top-card">
							<div class="card">
								<div class="card-header">
									Current Approval Status
								</div>
								<div class="card-body">
									<?php
									$currentLevel = $approvalRequest->currentLevel(true);
									$nextLevel = $approvalRequest->approval->levels->where('level',$currentLevel->level+1)->where('status',1)->first();
									$currentLevelStatus = $approvalRequest->currentLevel();
									$do_swap = $currentLevel && $approvalRequest->approval->do_swap && $approvalRequest->completed == 0;
									if($do_swap && !$approvalRequest->approvals->where('is_approved',1)->first()){										
										$do_swap = false;										
									}
									$user_allowed = (in_array(auth()->id(), $currentLevel->approval_users->where('status',1)->pluck('user_id')->all()) !== false && !$approvalRequest->approvers->where('user_id',auth()->id())->where('level',$currentLevel->level)->where('status',0)->first());
									$approval_properties = $approvalRequest->approval->properties;
		                            if($approval_properties)
		                                $approval_properties = json_decode($approval_properties);
									?>
									State: {{$currentLevelStatus}}<br>
									@if($currentLevelStatus != 'Pending' && $currentLevelStatus != 'Completed' && $currentLevelStatus != 'Rejected' && $currentLevelStatus != 'Declined')
									Users: {{($currentLevel != null) ? $currentLevel->users->where('status',1)->pluck('name')->join(', ') : ''}}<br>
									Submitted: {{$approvalRequest->approvers->where('level',($currentLevel != null) ? $currentLevel->level : null)->count()}}<br>								
									Next Level User Selection: {{$currentLevel->next_level_user == 0 ? 'No' : 'Yes'}}
									@if($currentLevel && ($user_allowed || $user_selection))
									<script type="text/javascript">
										var currentLevel = {!!json_encode($currentLevel)!!};
									</script>
									<br><button data-toggle="modal" data-target="#approval-modal" type="button" id="submit-approval" class="btn btn-sm btn-success">Submit Approval</button>
									@endif
									@else
									Time: {{approvalDate($approvalRequest->updated_at,true)}}
									@endif
									@if($do_swap && ($user_allowed || $user_selection))
									 <button data-toggle="modal" data-target="#swap-level-modal" type="button" id="swap-level" class="btn btn-sm btn-warning">Forward Level</button>
									@endif
									@if($currentLevel && ($user_allowed || $user_selection))
									<button data-toggle="modal" data-target="#comment-level-modal" type="button" id="comment-level" class="btn btn-sm btn-info">Comment</button>
									@endif
								</div>
							</div>							
						</div>
						@if(isset($approval_properties->partial))
		                @include($approval_properties->partial->view,[$approval_properties->partial->param => $approvalRequest])
		                @endif
						@if($approvalRequest->approval->on_update)
							@foreach($approvalRequest->mappings as $keyM => $valueM)
							<?php
							$currentItem = $valueM->approvable;
							?>
							<div class="col-12">
								<h5>{{$valueM->title}}</h5>
							</div>
							<div class="col-12">
								<table class="table table-sm table-bordered table-striped">
									<thead>
										<tr>
											<th>#SL</th>
											<th>Field</th>
											<th>Current Data</th>
											<th>New Data</th>
										</tr>
									</thead>
									<tbody>
										@foreach($valueM->form_data as $keyMF => $valueMF)
											<?php
											$fieldName = $valueMF->field_name;
											$currentFieldData = '';
											$newFieldData = '';
											$fieldRelation = json_decode($valueMF->field_relation);
											if($valueMF->field_type == 'text'){
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}elseif ($valueMF->field_type == 'email') {
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}elseif ($valueMF->field_type == 'textarea') {
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}elseif ($valueMF->field_type == 'date') {
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}
											elseif ($valueMF->field_type == 'file') {
												$currentFieldData = ($currentItem) ? '<a href="'.asset($currentItem->$fieldName).'">'.basename($currentItem->$fieldName).'</a>' : '';
												$newFieldData = '<a href="'.asset($valueMF->field_data).'">'.basename($valueMF->field_data).'</a>';
											}elseif ($valueMF->field_type == 'select' && is_object($fieldRelation)){												
												$fieldRelation->values = collect($fieldRelation->values);
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$currentFieldData = ($currentFieldData != '' && $fieldRelation->values->where('key',$currentFieldData)->first()) ? $fieldRelation->values->where('key',$currentFieldData)->first()->value : '';
												$newFieldData = $fieldRelation->values->where('key',$valueMF->field_data)->first();
												if($newFieldData)
													$newFieldData = $newFieldData->value;												
											}elseif (($valueMF->field_type == 'select' || $valueMF->field_type == 'select_single') && $valueMF->field_relation != '' && $valueMF->field_relation_pk != '' && $valueMF->field_relation_show != '') {
												$relationName = $valueMF->field_relation;
												$relationShow = $valueMF->field_relation_show;
												$currentFieldData = (($currentItem && $currentItem->$relationName) ? $currentItem->$relationName->$relationShow : (($currentItem) ? $currentItem->$fieldName : ''));
												
												$itemModel = $valueM->approvable_type;
												$itemRelation = $relationName;
												$itemRelationPK = $valueMF->field_relation_pk;
												$itemRelationShow = $valueMF->field_relation_show;
												$itemObject = new $itemModel();
												$itemRelationObject = $itemObject->$itemRelation()->getRelated();
												$itemRelationObjectType = strtolower(namespaceBasePath(get_class($itemRelationObject)));
												$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
												if($itemRelationObject::find($valueMF->field_data) && !$input_multiple)
													$newFieldData = $itemRelationObject::find($valueMF->field_data)->$relationShow;
												else
													$newFieldData = $valueMF->field_data;
											}
											
											$isChanged = ($currentFieldData != $newFieldData);
											?>
											<tr>
												<td>{{$keyMF+1}}</td>
												<td>{{$valueMF->field_label}}</td>
												<td>{!!$currentFieldData!!}</td>
												<td class="{{($isChanged) ? 'data-changed' : ''}}">{!!$newFieldData!!}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
							@endforeach
						@endif
						@foreach($approvalRequest->approvers->sortBy('level')->groupBy('level')->all() as $keyAL => $valueAL)
						<div class="col-12 mt-4">
							<h5>Approval Submissions for {{$valueAL[0]->title}}</h5>
							<table class="table table-sm table-bordered table-striped">
								<thead>
									<tr>
										<th>SL</th>
										<th>Approver</th>
										<th>Submission</th>
										@if($valueAL->where('next_level_user',1)->first())
										<th>Next Level User</th>
										@endif
										<th>Date</th>
										<th>Status</th>
										<th>Remarks</th>
									</tr>
								</thead>
								<tbody>
									@foreach($valueAL->sortByDesc('id')->values()->all() as $keyALS => $valueALS)
									<tr>
										<td width="40">{{$keyALS+1}}</td>
										<td>{{$valueALS->user[config('approval-config.user-name')]}}</td>
										<td>{{($valueALS->is_approved) ? 'Approved' : 'Rejected'}}</td>
										@if($valueAL->where('next_level_user',1)->first())
										<td>{{($valueALS->next_user) ? $valueALS->next_user[config('approval-config.user-name')] : ''}}</td>
										@endif
										<td width="150">{{approvalDate($valueALS->created_at)}}</td>
										<td width="50">{{$valueALS->status ? 'Done' : 'Pending'}}</td>
										<td>
											{{$valueALS->reason}}
											@if(is_array($valueALS->reason_file))
												@foreach($valueALS->reason_file as $keyAF => $valueAF)
													<br><a href="{{asset($valueAF)}}">{{basename($valueAF)}}</a>
												@endforeach
												@foreach($valueALS->forms as $keyAFS => $valueAFS)
													<br><b>{{$valueAFS->title}}</b>
													@foreach($valueAFS->form_data as $keyAFSS => $valueAFSS)
														<?php
														$fieldRelation = json_decode($valueAFSS->mapped_field_relation);
														?>
														@if($valueAFSS->mapped_field_type == 'text')
															<br>{{$valueAFSS->mapped_field_label.' : '.$valueAFSS->mapped_field_value}}
														@elseif($valueAFSS->mapped_field_type == 'email')
															<br>{{$valueAFSS->mapped_field_label.' : '.$valueAFSS->mapped_field_value}}
														@elseif($valueAFSS->mapped_field_type == 'file')
															<br><a href="{{asset($valueAFSS->mapped_field_value)}}">{{basename($valueAFSS->mapped_field_value)}}</a>
														@elseif ($valueAFSS->mapped_field_type == 'select' && is_object($fieldRelation))
														<?php
															$fieldRelation->values = collect($fieldRelation->values);
															$newFieldData = $fieldRelation->values->where('key',$valueAFSS->mapped_field_value)->first();
															if($newFieldData)
																$newFieldData = $newFieldData->value;															
														?>
															<br>{{$valueAFSS->mapped_field_label.' : '.$newFieldData}}
														@elseif(($valueAFSS->mapped_field_type == 'select' || $valueAFSS->mapped_field_type == 'select_single') && $valueAFSS->mapped_field_relation != "" && $valueAFSS->mapped_field_relation_pk != "" && $valueAFSS->mapped_field_relation_show != "")
															<?php
															$itemModel = $valueAFS->approvable_type;
															$itemRelation = $valueAFSS->mapped_field_relation;
															if(strpos($itemRelation, 'self:') !== false){
                                                                $itemRelation = str_replace('self:','',$itemRelation);
                                                            }
															$itemRelationPK = $valueAFSS->mapped_field_relation_pk;
															$itemRelationShow = $valueAFSS->mapped_field_relation_show;
															$itemObject = new $itemModel();
															$itemRelationObject = $itemObject->$itemRelation()->getRelated();													
															?>
															@if($valueAFS->approvable_type == $approvalRequest->approvable_type)
															<br>{{$valueAFSS->mapped_field_label.' : '.$itemRelationObject->where($itemRelationPK,$valueAFSS->mapped_field_value)->first()->$itemRelationShow}}
															@endif
														@endif
													@endforeach
													<br>
												@endforeach
											@endif
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
						@endforeach
						@if($approvalRequest->approvals)
						<div class="col-12 mt-4">
							<h5>Approval Submissions Log</h5>
							<table class="table table-sm table-bordered table-striped">
								<thead>
									<tr>
										<th>SL</th>
										<th>Approver</th>
										<th>Previous Level</th>
										<th>Submission Level</th>
										<th>Submission</th>
										<th>Date</th>
										<th>Remarks</th>
									</tr>
								</thead>
								<tbody>
								@foreach($approvalRequest->approvals as $keyARL => $valueARL)
									<tr>
										<td>{{$keyARL+1}}</td>
										<td>{{$valueARL->user[config('approval-config.user-name')]}}</td>
										<td>{{$valueARL->prev_level_title}}</td>
										<td>{{$valueARL->next_level_title}}</td>
										<td>{{get_approval_type($valueARL)}}</td>
										<td width="150">{{approvalDate($valueARL->created_at)}}</td>
										<td>{{$valueARL->reason}}</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						</div>
						@endif
					</div>
				</div>
			</div>
		</div>		
		<style type="text/css">
			.top-card .card-body{
				min-height: 170px !important;
			}
			.data-changed{
				border-bottom: 1px solid red !important;
			}
		</style>
		<div class="modal" tabindex="-1" id="approval-data-modal">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title">Approval Data</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">
			  </div>
			  <div class="modal-footer justify-content-between">
				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>	              
			  </div>
			</div>
		  </div>
		</div>
		@if($do_swap)
		<div class="modal" tabindex="-1" id="swap-level-modal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
					<h5 class="modal-title">Forward Level</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					</div>
					<div class="modal-body">
						<form method="POST" action="{{route(config('approval-config.route-name-request-prefix').'.swap_level',['approvalRequest' => $approvalRequest->id])}}">
							@csrf
							<?php
							$maxLevelApproved = $approvalRequest->approvals->where('is_approved',1)->sortByDesc('next_level')->first();
							if($currentLevel->move_on_reject){
		                        $maxLevelApproved = $approvalRequest->approvals->sortByDesc('next_level')->first();                        
		                    }
							?>
							@if($maxLevelApproved)
							<?php
							$maxLevelList = $approvalRequest->approval->levels->where('level','!=',$currentLevel->level)->where('level','<=',$maxLevelApproved->next_level+1)->all();
							?>
							<textarea id="swap-reason" name="swap_reason" placeholder="Remarks" class="form-control mb-3" required></textarea>
							<select class="form-control mb-3" name="do_swap" required>
								@foreach($maxLevelList as $keyALSI => $valueALSI)
								<option value="{{$valueALSI->level}}">{{$valueALSI->title}}</option>
								@endforeach
							</select>
							@endif
							<button type="submit" class="btn btn-primary">Save changes</button>
						</form>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>	              
					</div>
				</div>
			</div>
		</div>
		@endif
		@if($currentLevel)
		<div class="modal" tabindex="-1" id="approval-modal">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title">Modal title</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">
				<form method="POST" enctype="multipart/form-data" action="{{route(config('approval-config.route-name-request-prefix').'.submit',['approvalRequest' => $approvalRequest->id])}}" onsubmit="return chkApprovalValidate()">
					@csrf
					@php
					$approval_level_properties = (object)json_decode($currentLevel->properties);					
					@endphp					
					<textarea id="approval-reason" name="approval_reason" placeholder="Reason" class="form-control mb-3"></textarea>
					@if($currentLevel->need_attachment)
					<input type="file" multiple name="approval_file[]" class="form-control mb-3">		        
					@endif
					@if($approval_level_properties && isset($approval_level_properties->partial_form))
						@include($approval_level_properties->partial_form->view,[$approval_level_properties->partial_form->param => $approvalRequest])
					@endif									
					@if($nextLevel && $currentLevel->next_level_user)
					<select name="approval_next_user" class="form-control mb-3" id="approval-next-user">
						<option value="">Select {{$nextLevel->title}} User</option>
						@foreach($nextLevel->users as $nextUser)
						<option value="{{$nextUser[config('approval-config.user-primary-key')]}}">{{$nextUser[config('approval-config.user-name')]}}</option>
						@endforeach
					</select>
					@endif	        
					@if($currentLevel->forms)
						@foreach($currentLevel->forms as $keyAF => $valueAF)
							<label class="approval-form">{{$valueAF->title}}</label><br>
							@if($valueAF->approvable_type == $approvalRequest->approval->approvable_type)
								@foreach($valueAF->form_data as $keyAFS => $valueAFS)
									<?php
									$fieldName = $valueAFS->mapped_field_name;
									$fieldRelation = json_decode($valueAFS->mapped_field_relation);
									?>
									<label class="approval-form">{{$valueAFS->mapped_field_label}}:</label>
									@if($valueAFS->mapped_field_type == 'text')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalRequest->approvable->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif($valueAFS->mapped_field_type == 'email')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalRequest->approvable->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif($valueAFS->mapped_field_type == 'file')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif($valueAFS->mapped_field_type == 'date')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif ($valueAFS->mapped_field_type == 'select' && is_object($fieldRelation))
									<?php
										$fieldRelation->values = collect($fieldRelation->values);
										$input_name = $valueAF->id.'_'.$fieldName.($fieldRelation->type == 'multiple' ? '[]' : '');
									?>
										<select class="form-control approval-form mb-3" name="{{$input_name}}" {{(($fieldRelation->type == 'multiple') ? 'multiple' : '')}}>
										@foreach($fieldRelation->values as $keyAFSR => $valueAFSR)
											<option value="{{$valueAFSR->key}}" {{(($valueAFSR->key == $approvalRequest->approvable->$fieldName) ? 'selected' : '')}}>{{$valueAFSR->value}}</option>
										@endforeach
										</select>
									@elseif($valueAFS->mapped_field_type == 'select' && $valueAFS->mapped_field_relation != "" && $valueAFS->mapped_field_relation_pk != "" && $valueAFS->mapped_field_relation_show != "")
										@if($valueAF->approvable_type == $approvalRequest->approval->approvable_type)
											<?php
											$itemModel = $valueAF->approvable_type;
											$itemRelation = $valueAFS->mapped_field_relation;
											$itemRelationPK = $valueAFS->mapped_field_relation_pk;
											$itemRelationShow = $valueAFS->mapped_field_relation_show;
											$itemObject = new $itemModel();
											if(strpos($itemRelation, 'self:') !== false){
		                                        $itemRelation = str_replace('self:','',$itemRelation);
		                                        $itemObject = $approvalRequest->approvable;
		                                        $dropdownCollection = $approvalRequest->approvable->$itemRelation;
		                                    }else{
		                                        $dropdownCollection = $itemObject->$itemRelation()->getRelated()::get();
		                                    }
											$itemRelationObject = $itemObject->$itemRelation();
											$itemRelationObjectType = strtolower(namespaceBasePath(get_class($itemRelationObject)));
											$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
											$input_name = $valueAF->id.'_'.$fieldName.($input_multiple ? '[]' : '');
											?>
											<select class="form-control approval-form mb-3" name="{{$input_name}}" {{(($input_multiple) ? 'multiple' : '')}}>
											@foreach($dropdownCollection as $keyAFSR => $valueAFSR)
												<option value="{{$valueAFSR->$itemRelationPK}}" {{(($valueAFSR->$itemRelationPK == $approvalRequest->approvable->$fieldName) ? 'selected' : '')}}>{{$valueAFSR->$itemRelationShow}}</option>
											@endforeach
											</select>
										@endif
									@elseif($valueAFS->mapped_field_type == 'select_single' && $valueAFS->mapped_field_relation != "" && $valueAFS->mapped_field_relation_pk != "" && $valueAFS->mapped_field_relation_show != "")
		                                @if($valueAF->approvable_type == $approvalRequest->approval->approvable_type)
		                                    <?php
		                                    $itemModel = $valueAF->approvable_type;
		                                    $itemRelation = $valueAFS->mapped_field_relation;                                    
		                                    $itemRelationPK = $valueAFS->mapped_field_relation_pk;
		                                    $itemRelationShow = $valueAFS->mapped_field_relation_show;
		                                    $itemObject = new $itemModel();
		                                    if(strpos($itemRelation, 'self:') !== false){
		                                        $itemRelation = str_replace('self:','',$itemRelation);
		                                        $itemObject = $approvalRequest->approvable;
		                                        $dropdownCollection = $approvalRequest->approvable->$itemRelation;
		                                    }else{
		                                        $itemRelationObject = $itemObject->$itemRelation();
		                                        $dropdownCollection = $itemRelationObject->getRelated()::get();
		                                    }                                                                        
		                                    $input_name = $valueAF->id.'_'.$fieldName;
		                                    ?>
		                                    <select class="form-control approval-form mb-3" name="{{$input_name}}">
		                                    @foreach($dropdownCollection as $keyAFSR => $valueAFSR)
		                                        <option value="{{$valueAFSR->$itemRelationPK}}" {{(($valueAFSR->$itemRelationPK == $approvalRequest->approvable->$fieldName) ? 'selected' : '')}}>{{$valueAFSR->$itemRelationShow}}</option>
		                                    @endforeach
		                                    </select>
		                                @endif
		                            @endif
								@endforeach
							@else
								@foreach($valueAF->form_data as $keyAFS => $valueAFS)
									<?php
									$approvable_typeR = $valueAF->approvable_type;
									$approvalItemR = $approvalRequest->approvable->{$valueAF->relation};											
									$fieldName = $valueAFS->mapped_field_name;
									$fieldRelation = json_decode($valueAFS->mapped_field_relation);											
									?>
									<label class="approval-form">{{$valueAFS->mapped_field_label}}:</label>
									@if($valueAFS->mapped_field_type == 'text')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalItemR->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif($valueAFS->mapped_field_type == 'email')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalItemR->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif($valueAFS->mapped_field_type == 'file')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif($valueAFS->mapped_field_type == 'date')
										<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalItemR->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
									@elseif ($valueAFS->mapped_field_type == 'select' && is_object($fieldRelation))
										<?php
										$fieldRelation->values = collect($fieldRelation->values);
										$input_name = $valueAF->id . '_' . $fieldName . ($fieldRelation->type == 'multiple' ? '[]' : '');
										?>
										<select class="form-control approval-form mb-3" name="{{$input_name}}" {{(($fieldRelation->type == 'multiple') ? 'multiple' : '')}}>
											@foreach($fieldRelation->values as $keyAFSR => $valueAFSR)
												<option value="{{$valueAFSR->key}}" {{(($valueAFSR->key == $approvalItemR->$fieldName) ? 'selected' : '')}}>{{$valueAFSR->value}}</option>
											@endforeach
										</select>
									@elseif($valueAFS->mapped_field_type == 'select' && $valueAFS->mapped_field_relation != "" && $valueAFS->mapped_field_relation_pk != "" && $valueAFS->mapped_field_relation_show != "")
										
										<?php
										$itemModel = $valueAF->approvable_type;
										$itemField = $valueAFS->mapped_field_name;
										$itemRelation = $valueAFS->mapped_field_relation;
										$itemRelationPK = $valueAFS->mapped_field_relation_pk;
										$itemRelationShow = $valueAFS->mapped_field_relation_show;
										$itemObject = new $itemModel();
										$itemRelationObject = $itemObject->$itemRelation();
										$itemRelationObjectType = strtolower(basename(get_class($itemRelationObject)));
										$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
										$input_name = $valueAF->id . '_' . $fieldName . ($input_multiple ? '[]' : '');
										?>
										<select class="form-control approval-form mb-3" name="{{$input_name}}" {{(($input_multiple) ? 'multiple' : '')}}>
											@foreach($itemRelationObject->getRelated()::get() as $keyAFSR => $valueAFSR)
												<option value="{{$valueAFSR->$itemRelationPK}}" {{(($valueAFSR->$itemRelationPK == $approvalItemR->$fieldName) ? 'selected' : '')}}>{{$valueAFSR->$itemRelationShow}}</option>
											@endforeach
										</select>
										
									@endif
								@endforeach
							@endif
						@endforeach
					@endif
					@if($approval_level_properties && isset($approval_level_properties->hide_action))
                    <input type="hidden" id="approval-option" name="approval_option" value="1">
                    @else
                    <select name="approval_option" class="w-full form-input mb-3 border px-4 py-3 rounded-[8px]" id="approval-option">
                        <option value="1">Approve</option>
                        <option value="2">Send Back</option>
                        <option value="0">Reject</option>
                    </select>
                    @endif
					<button type="submit" class="btn btn-primary">Save changes</button>
				</form>
			  </div>
			  <div class="modal-footer justify-content-between">
				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>	              
			  </div>
			</div>
		  </div>
		</div>
		<div class="modal" tabindex="-1" id="comment-level-modal">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title">Approval Comments</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">
	  			<form method="POST" action="{{route(config('approval-config.route-name-request-prefix').'.comment_level',['approvalRequest' => $approvalRequest->id])}}">
					@csrf					
					<textarea id="level-comment" name="level_comment" placeholder="Comments" class="form-control mb-3" required></textarea>
					<button type="submit" class="btn btn-primary">Save changes</button>
				</form>
			  </div>
			  <div class="modal-footer justify-content-between">
				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>	              
			  </div>
			</div>
		  </div>
		</div>
		<script type="text/javascript">
			function chkApprovalValidate(){
				if(parseInt(currentLevel.is_approve_reason_required) == 1 && $('#approval-option').val() == 1 && $('#approval-reason').val().trim().length == 0){
					alert("Remarks is required!");
					return false;
				}
				if(parseInt(currentLevel.is_reject_reason_required) == 1 && $('#approval-option').val() == 0 && $('#approval-reason').val().trim().length == 0){
					alert("Remarks is required!");
					return false;
				}				
			}
		</script>
		@endif
@endsection
@push(config('approval-config.script-stack'))
	@if(config('approval-config.load-script'))
	@include('laravel-approval::partials.script')
	@endif
	<script type="text/javascript">
		$(document).on("click","#submit-approval",function(){
			$('#approval-modal .modal-title').html("Approval For: "+currentLevel.title);
		});

		$(document).on("change","#approval-option",function(){
			var $item = $(this);
			$('#approval-next-user').toggle();
			if($item.val() == 1){
				$('.approval-form').show();
				$(':input.approval-form').removeAttr('required').attr('required','required');
			}else{
				$('input.approval-form').removeAttr('required');
				$('.approval-form').hide();
			}
		});		
	</script>
@endpush
