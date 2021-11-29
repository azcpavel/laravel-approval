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
									Total Approvals: {{$approvalRequest->approvers->count()}}<br>
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
									?>
									Level: {{$approvalRequest->currentLevel()}}<br>
									Users: {{($currentLevel != null) ? $currentLevel->users->pluck('name')->join(',') : ''}}<br>
									Submitted: {{$approvalRequest->approvers->where('level',($currentLevel != null) ? $currentLevel->level : null)->count()}}
									
									@if($currentLevel && in_array(auth()->id(), $currentLevel->users->pluck('id')->all()) !== false && !$approvalRequest->approvers->where('user_id',auth()->id())->where('level',$currentLevel->level)->where('status',0)->first())
									<script type="text/javascript">
										var currentLevel = {!!json_encode($currentLevel)!!};
									</script>
									<br><button data-toggle="modal" data-target="#approval-modal" data-whatever="@mdo" type="button" id="submit-approval" class="btn btn-sm btn-success">Submit Approval</button>
									@endif
								</div>
							</div>							
						</div>
						@foreach($approvalRequest->approval->levels as $keyAL => $valueAL)
						<div class="col-12 mt-4">
							<h5>Approval Submissions for {{$valueAL->title}}</h5>
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
									@foreach($approvalRequest->approvers->where('level',$valueAL->level)->sortByDesc('id')->values()->all() as $keyALS => $valueALS)
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
		</style>
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
	</script>
@endpush
