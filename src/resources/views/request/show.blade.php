@extends(config('approval-config.view-layout'))
@section(config('approval-config.view-section'))
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
									{{$approvalRequest->approvable->$valueFN}}</br>
									@endforeach
									{{approvalDate($approvalRequest->created_at, true)}}<br>
									<a target="_blank" href="{{route($approvalRequest->approval->view_route_name,[array_keys($approvalRequest->approval->view_route_param)[0]=>$approvalRequest->approvable[array_values($approvalRequest->approval->view_route_param)[0]]])}}">View Details</a>
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
									$currentLevelStatus = $approvalRequest->currentLevel();
									?>
									Level: {{$currentLevelStatus}}<br>
									@if($currentLevelStatus != 'Pending' && $currentLevelStatus != 'Completed')
									Users: {{($currentLevel != null) ? $currentLevel->users->where('status',1)->pluck('name')->join(',') : ''}}<br>
									Submitted: {{$approvalRequest->approvers->where('level',($currentLevel != null) ? $currentLevel->level : null)->count()}}									
									@if($currentLevel && in_array(auth()->id(), $currentLevel->approval_users->where('status',1)->pluck('user_id')->all()) !== false && !$approvalRequest->approvers->where('user_id',auth()->id())->where('level',$currentLevel->level)->where('status',0)->first())
									<script type="text/javascript">
										var currentLevel = {!!json_encode($currentLevel)!!};
									</script>
									<br><button data-toggle="modal" data-target="#approval-modal" type="button" id="submit-approval" class="btn btn-sm btn-success">Submit Approval</button>
									@endif
									@else
									Time: {{approvalDate($approvalRequest->updated_at)}}
									@endif
								</div>
							</div>							
						</div>
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
											if($valueMF->field_type == 'text'){
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}elseif ($valueMF->field_type == 'email') {
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}elseif ($valueMF->field_type == 'textarea') {
												$currentFieldData = ($currentItem) ? $currentItem->$fieldName : '';
												$newFieldData = $valueMF->field_data;
											}elseif ($valueMF->field_type == 'file') {
												$currentFieldData = ($currentItem) ? '<a href="'.asset($currentItem->$fieldName).'">'.basename($currentItem->$fieldName).'</a>' : '';
												$newFieldData = '<a href="'.asset($valueMF->field_data).'">'.basename($valueMF->field_data).'</a>';
											}elseif ($valueMF->field_type == 'select' && $valueMF->field_relation != '' && $valueMF->field_relation_pk != '' && $valueMF->field_relation_show != '') {
												$relationName = $valueMF->field_relation;
												$relationShow = $valueMF->field_relation_show;
												$currentFieldData = (($currentItem && $currentItem->$relationName) ? $currentItem->$relationName->$relationShow : (($currentItem) ? $currentItem->$fieldName : ''));
												
												$itemModel = $currentItem;
												$itemRelation = $relationName;
												$itemRelationPK = $valueMF->field_relation_pk;
												$itemRelationShow = $valueMF->field_relation_show;
												$itemObject = new $itemModel();
												$itemRelationObject = $itemObject->$itemRelation()->getRelated();
												if($itemRelationObject::find($valueMF->field_data))
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
										<th>Date</th>
										<th>Remarks</th>
									</tr>
								</thead>
								<tbody>
									@foreach($valueAL->sortByDesc('id')->values()->all() as $keyALS => $valueALS)
									<tr>
										<td width="40">{{$keyALS+1}}</td>
										<td>{{$valueALS->user->name}}</td>
										<td>{{($valueALS->is_approved) ? 'Approved' : 'Rejected'}}</td>
										<td width="150">{{approvalDate($valueALS->created_at)}}</td>
										<td>
											{{$valueALS->reason}}
											@if(is_array($valueALS->reason_file))
												@foreach($valueALS->reason_file as $keyAF => $valueAF)
													<br><a href="{{asset($valueAF)}}">{{basename($valueAF)}}</a>
												@endforeach
												@foreach($valueALS->forms as $keyAFS => $valueAFS)
											        <br><b>{{$valueAFS->title}}</b>
											        @foreach($valueAFS->form_data as $keyAFSS => $valueAFSS)
												        @if($valueAFSS->mapped_field_type == 'text')
												        	<br>{{$valueAFSS->mapped_field_label.' : '.$valueAFSS->mapped_field_value}}
												        @elseif($valueAFSS->mapped_field_type == 'email')
												        	<br>{{$valueAFSS->mapped_field_label.' : '.$valueAFSS->mapped_field_value}}
												        @elseif($valueAFSS->mapped_field_type == 'file')
												        	<br><a href="{{asset($valueAFSS->mapped_field_value)}}">{{basename($valueAFSS->mapped_field_value)}}</a>
												        @elseif($valueAFSS->mapped_field_type == 'select' && $valueAFSS->mapped_field_relation != "" && $valueAFSS->mapped_field_relation_pk != "" && $valueAFSS->mapped_field_relation_show != "")
															<?php
															$itemModel = $valueAFS->approvable_type;
															$itemRelation = $valueAFSS->mapped_field_relation;
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
					</div>
				</div>
			</div>
		</div>		
		<style type="text/css">
			.top-card .card-body{
				min-height: 140px !important;
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
		      	<form method="POST" enctype="multipart/form-data" action="{{route('approval_request.submit',['approvalRequest' => $approvalRequest->id])}}" onsubmit="return chkApprovalValidate()">
			        @csrf
			        <textarea id="approval-reason" name="approval_reason" placeholder="Reason" class="form-control mb-3"></textarea>
			        <input type="file" multiple name="approval_file[]" class="form-control mb-3">		        
			        <select name="approval_option" class="form-control mb-3" id="approval-option">
			        	<option value="1">Approve</option>
			        	<option value="0">Reject</option>
			        </select>			        
			        @if($currentLevel->forms)
				        @foreach($currentLevel->forms as $keyAF => $valueAF)
					        <label class="approval-form">{{$valueAF->title}}</label><br>
					        @foreach($valueAF->form_data as $keyAFS => $valueAFS)
						        <?php
						        $fieldName = $valueAFS->mapped_field_name;
						    	?>
						    	<label class="approval-form">{{$valueAFS->mapped_field_label}}:</label>
						        @if($valueAFS->mapped_field_type == 'text')
						        	<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalRequest->approvable->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
						        @elseif($valueAFS->mapped_field_type == 'email')
						        	<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" value="{{$approvalRequest->approvable->$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
						        @elseif($valueAFS->mapped_field_type == 'file')
						        	<input type="{{$valueAFS->mapped_field_type}}" class="form-control approval-form mb-3" name="{{$valueAF->id.'_'.$fieldName}}" placeholder="{{$valueAFS->mapped_field_label}}" required>
						        @elseif($valueAFS->mapped_field_type == 'select' && $valueAFS->mapped_field_relation != "" && $valueAFS->mapped_field_relation_pk != "" && $valueAFS->mapped_field_relation_show != "")
									@if($valueAF->approvable_type == $approvalRequest->approval->approvable_type)
										<?php
										$itemModel = $valueAF->approvable_type;
										$itemRelation = $valueAFS->mapped_field_relation;
										$itemRelationPK = $valueAFS->mapped_field_relation_pk;
										$itemRelationShow = $valueAFS->mapped_field_relation_show;
										$itemObject = new $itemModel();
										$itemRelationObject = $itemObject->$itemRelation();
										$itemRelationObjectType = strtolower(basename(get_class($itemRelationObject)));
										$input_multiple = ((strpos($itemRelationObjectType,'many') !== false) ? 1 : 0);
										$input_name = $valueAF->id.'_'.$fieldName.($input_multiple ? '[]' : '');
										?>
										<select class="form-control approval-form mb-3" name="{{$input_name}}" {{(($input_multiple) ? 'multiple' : '')}}>
										@foreach($itemRelationObject->getRelated()::get() as $keyAFSR => $valueAFSR)
											<option value="{{$valueAFSR->$itemRelationPK}}" {{(($valueAFSR->$itemRelationPK == $approvalRequest->approvable->$fieldName) ? 'selected' : '')}}>{{$valueAFSR->$itemRelationShow}}</option>
										@endforeach
										</select>
									@endif
								@endif
					        @endforeach
				        @endforeach
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
		<script type="text/javascript">
			function chkApprovalValidate(){
				if(currentLevel.is_approve_reason_required && $('#approval-option').val() == 1 && $('#approval-reason').val().trim().length == 0){
					alert("Reason is required!");
					return false;
				}
				if(currentLevel.is_reject_reason_required && $('#approval-option').val() == 0 && $('#approval-reason').val().trim().length == 0){
					alert("Reason is required!");
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
