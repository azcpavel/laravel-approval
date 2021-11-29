<?php

namespace Exceptio\ApprovalPermission\Http\Controllers;

use Exceptio\ApprovalPermission\Models\{
	Approval,    
	ApprovalRequest,    
};
use Illuminate\Http\Request;

class ApprovalRequestController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, Approval $approval)
	{
		$approval = Approval::with('levels.forms.form_data','levels.users','mappings.fields')->where('id',$approval->id)->first();
		if($request->wantsJson()){
			$approvalRequest = new ApprovalRequest();
			$limit = 10;
			$offset = 0;
			$search = '';
			$where = [];
			$with = [];
			$join = [];
			$orderBy = [];

			if($request->input('length')){
				$limit = $request->input('length');
			}

			if ($request->input('order')[0]['column'] != 0) {
				$column_name = $request->input('columns')[$request->input('order')[0]['column']]['name'];
				$sort = $request->input('order')[0]['dir'];
				$orderBy[$column_name] = $sort;
			}

			if($request->input('start')){
				$offset = $request->input('start');
			}

			if($request->input('search') && $request->input('search')['value'] != ""){
				$search = $request->input('search')['value'];                
			}

			if($request->input('where')){
				$where = $request->input('where');
			}
			
			$where[] = ['approval_id',$approval->id];
			$fields = $approval->list_data_fields;
			if($approval->levels->count() > 0){
				foreach($approval->levels[0]->status_fields->pending as $keyF => $valueF){
					$fields[] = $keyF;
				}
			}
			$with[] = 'approvable:id,'.implode(',', $fields);
			$approvalRequest = $approvalRequest->getDataForDataTable($limit, $offset, $search, $where, $with, $join, $orderBy, $request->all(), null, $approval->list_data_fields);

			return response()->json($approvalRequest);
		}
		
		return view('laravel-approval::request.index',['approval' => $approval]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function show(ApprovalRequest $approvalRequest)
	{
		$approvalRequest = ApprovalRequest::where('id',$approvalRequest->id)
					->with('approval.levels.forms.form_data',
						'approval.levels.users',
						'approval.mappings.fields',
						'approvers.forms.form_data',
						'mappings.form_data',
						'approvable')->first();
		return view('laravel-approval::request.show',['approvalRequest' => $approvalRequest]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function edit(ApprovalRequest $approvalRequest)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, ApprovalRequest $approvalRequest)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(ApprovalRequest $approvalRequest)
	{
		//
	}

	/**
	 * Show the form for submit approval the specified resource.
	 *
	 * @param  \App\Models\ApprovalRequest  $approvalRequest
	 * @return \Illuminate\Http\Response
	 */
	public function submit(Request $request, ApprovalRequest $approvalRequest)
	{
		$currentLavel = $approvalRequest->currentLevel(true);
		if($request->has('approval_option')){            
			try{
				\DB::beginTransaction();

				$message['msg_type'] = 'success';

				$approvalItem = $approvalRequest->approvable;

				$uploadDirsBase = public_path();
				$uploadDirs = explode('/', config('approval-config.upload-dir'));                
				foreach($uploadDirs as $keyUD => $valueUD){
					$uploadDirsBase .= DIRECTORY_SEPARATOR.$valueUD;
					if(!file_exists($uploadDirsBase)){                        
						mkdir($uploadDirsBase);
					}
				}

				if(!file_exists($uploadDirsBase.DIRECTORY_SEPARATOR.'approvals')){                    
					mkdir($uploadDirsBase.DIRECTORY_SEPARATOR.'approvals');
				}
				$uploadDir = config('approval-config.upload-dir').DIRECTORY_SEPARATOR.'approvals';
				$files = [];
				if($request->hasFile('approval_file')){                    
					foreach($request->approval_file as $keyAF => $valueAF){
						$tempName = approvalFileName($valueAF->getClientOriginalName(), 1, $approvalRequest->id);                        
						$filePath = $valueAF->storeAs($uploadDir,$tempName);                        
						$files[] = $filePath;
					}
				}

				if($request->approval_option == 1){                    

					$approvalRequest->approvers()->create([
						'approval_id' => $currentLavel->approval_id,
						'user_id' => auth()->id(),
						'title' => $currentLavel->title,
						'is_flexible' => $currentLavel->is_flexible,
						'is_form_required' => $currentLavel->is_form_required,
						'level' => $currentLavel->level,
						'action_type' => $currentLavel->action_type,
						'action_data' => $currentLavel->action_data,
						'status_fields' => $currentLavel->status_fields,
						'is_data_mapped' => $currentLavel->is_data_mapped,
						'is_approved' => 1,
						'is_rejected' => 0,                        
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
				}else if($request->approval_option == 0){                    

					$approvalRequest->approvers()->create([
						'approval_id' => $currentLavel->approval_id,
						'user_id' => auth()->id(),
						'title' => $currentLavel->title,
						'is_flexible' => $currentLavel->is_flexible,
						'is_form_required' => $currentLavel->is_form_required,
						'level' => $currentLavel->level,
						'action_type' => $currentLavel->action_type,
						'action_data' => $currentLavel->action_data,
						'status_fields' => $currentLavel->status_fields,
						'is_data_mapped' => $currentLavel->is_data_mapped,
						'is_approved' => 0,
						'is_rejected' => 1,                        
						'reason' => $request->approval_reason,
						'reason_file' => $files,
					]);
				}

				$finalLevel = $approvalRequest->approval->levels->sortByDesc('level')->first();
				
				$complete = true;                    
				foreach($currentLavel->users as $keyAU => $valueAU){
					$isSubmitted = $approvalRequest->approvers->where('level',$currentLavel->level)->where('user_id',$valueAU->id)->where('status',0)->first();
					if(!$isSubmitted){
						$complete = false;
						break;
					}
				}

				if($complete){
					$approveCount = 0;
					$rejectCount = 0;
					foreach($approvalRequest->approvers->where('level',$currentLavel->level)->where('status',0)->all() as $keyASD => $valueASD){
						$valueASD->update([
							'status' => 1
						]);

						if($valueASD->is_approved)
							$approveCount++;
						if($valueASD->is_rejected)
							$rejectCount++;
					}

					if($currentLavel->is_flexible == 0){
						if($rejectCount == 0){
							foreach($currentLavel->status_fields->approve as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
							$message['msg_data'] = 'Your approval has been submitted';
							if($finalLevel->id == $currentLavel->id && $complete){
								$approvalRequest->completed = 1;
								$approvalRequest->save();
							}
						}else{
							foreach($currentLavel->status_fields->reject as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
							$message['msg_data'] = 'Your rejection has been submitted';
						}
					}else{
						if($approveCount >= $currentLavel->is_flexible){
							foreach($currentLavel->status_fields->approve as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
							$message['msg_data'] = 'Your approval has been submitted';
							if($finalLevel->id == $currentLavel->id && $complete){
								$approvalRequest->completed = 1;
								$approvalRequest->save();
							}
						}else{
							foreach($currentLavel->status_fields->reject as $keyA => $valueA){
								$approvalItem->$keyA = $valueA;
							}
							$approvalItem->save();
							$message['msg_data'] = 'Your rejection has been submitted';
						}
					}
				
				}				
				
				\DB::commit();
			}catch(\Exception $e){
				if(env('APP_DEBUG'))
					dd($e);
				\DB::rollback();
				$message['msg_type'] = 'danger';
				$message['msg_data'] = 'Something went wrong, please try again';
			}            
			return redirect()->back()->with($message);
		}else{
			$message['msg_type'] = 'danger';
			return redirect()->back()->with($message);
		}
	}
}
