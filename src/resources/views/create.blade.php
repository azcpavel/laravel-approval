@extends(config('approval-config.view-layout'))
@section(config('approval-config.view-section'))		
		<div class="flex-center position-ref full-height">            
			<div class="content">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-12 col-sm-8 col-md-6">
							<form class="form-horizontal" action="{{route('approvals.store')}}" method="post">
								@csrf
								<div class="form-group">
									<label for="title">Title <span class="text-danger position-relative">*</span></label>
									<input class="form-control" type="text" name="title" placeholder="Title" id="title" required>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group">
									<label for="approval_type">Approval Type<span class="text-danger position-relative">*</span></label>
									<select class="form-control" name="approval_type" id="approval_type">
										<option value="1">Create</option>
										<option value="2">Update</option>
										<option value="3">Delete</option>
									</select>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="form-group">
									<label for="model_namespace">Model <span class="text-danger position-relative">*</span></label>
									<select class="form-control" name="model_namespace">
										@foreach($models as $model_file)
										<option value="{{str_replace('.php','',$model_file)}}">{{str_replace('.php','',basename($model_file))}}</option>
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
										<div class="row mt-2 list_data_fields_div_item">
											<div class="col-8">
												<input class="form-control" type="text" name="list_data_fields[]" placeholder="List Data Fields" required>	
											</div>
											<div class="col-4 text-right">
												<button type="button" class="btn btn-danger list_data_fields_btn_remove">Remove</button>
											</div>
										</div>										
									</div>
									<span class="d-none invalid-feedback"></span>
								</div>
								<div class="row" id="model_namespace_relation_div" style="display:none;">
									<div class="col-12">
										<div class="row form-group">
											<div class="col-12">
											<label for="title">Model Update Fields<span class="text-danger position-relative">*</span></label>											
											</div>
											<div class="col-8">
												<select class="form-control" id="model_namespace_relation">
													@foreach($models as $model_file)
													<option value="{{str_replace('.php','',$model_file)}}">{{str_replace('.php','',basename($model_file))}}</option>
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
										
									</div>
								</div>
								<div class="form-group">
									<label for="view_route_name">View Route<span class="text-danger position-relative">*</span></label>
									<input class="form-control" type="text" name="view_route_name" placeholder="Route name" id="view_route" required>
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
										<div class="row mt-2 view_route_param_div_item">
											<div class="col-8 input-group">
												<input class="form-control" type="text" name="view_route_param_key[]" placeholder="Param Name" required>	
												<input class="form-control" type="text" name="view_route_param_value[]" placeholder="Column Name" required>	
											</div>
											<div class="col-4 text-right">
												<button type="button" class="btn btn-danger view_route_param_btn_remove">Remove</button>
											</div>
										</div>										
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
@endsection
@push('script')
@if(config('approval-config.load-script'))
@include('laravel-approval::partials.script')
@endif
<script type="text/javascript">
	$(document).on("change","#model_namespace_relation_input",function(){
		var $input = $(this);
		$('#model_namespace_relation_div').toggle();
	});

	$(document).on("change","#approval_type",function(){
		var $input = $(this);
		if($input.val() == 2){
			$('#model_namespace_relation_div').show();
		}else{			
			$('#model_namespace_relation_div').hide();
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
				'<input type="text" name="model_namespace_relation_title[]" class="float-left form-control model_namespace_relation_title" placeholder="Relation Title" required>'+
				'<input type="text" name="model_relation_path[]" class="float-left my-3 form-control model_relation_path" placeholder="Relation Path" required>'+
			'</div>');
		$.ajax({
			'url' : '{{route('approvals.model_info')}}?model_namespace='+inputVal,
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
						'<input type="text" name="model_namespace_relation_tbody_relation['+inputKey+'][]" class="form-control model_namespace_relation_tbody_relation mt-2" placeholder="Relation">'+
						'<select class="mt-2 form-control model_namespace_relation_tbody_type" name="model_namespace_relation_tbody_type['+inputKey+'][]" required><option value="text">Text</option><option value="number">Number</option><option value="email">Email</option><option value="textarea">Textarea</option><option value="file">File</option></select></td>'+
						'<td><input type="checkbox" name="model_namespace_relation_tbody_check['+inputKey+'][]" class="model_namespace_relation_tbody_check" value="'+indKey+'"></td>'+
					'</tr>'
				);
			});
			$('#model_namespace_relation_div_item').append($htmlWrap);
		});		
	});

	$(document).on("click","#approval_level_add",function(){
		$.ajax({
			url : '{{route('approvals.model_level_form')}}',
			'type' : 'GET',
			'dataType' : 'html'
		}).done(function(response){
			var $htmlData = $(response.replace('APPROVAL_LEVEL',$('.approval_level_item').length + 1).replace('APPROVE_USER_INDEX',$('.approval_level_item').length));
			$('#approval_level').append($htmlData);
			$htmlData.find('.select2').select2();
		});
		
	});

	
	$(document).on("change",".approval_form",function(){
		var $input = $(this);
		$input.closest('.approval_level_item').find('.approval_form_div').toggle();
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
				'<input type="text" name="approval_form_title['+approvalLevel+'][]" class="float-left my-3 form-control approval_form_title" placeholder="Relation Title" required>'+
			'</div>');
		$.ajax({
			'url' : '{{route('approvals.model_info')}}?model_namespace='+inputVal,
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
						'<select class="mt-2 form-control approval_form_tbody_type" name="approval_form_tbody_type['+approvalLevel+']['+inputKey+'][]" required><option value="text">Text</option><option value="number">Number</option><option value="email">Email</option><option value="textarea">Textarea</option><option value="file">File</option></select></td>'+
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
			$input.closest('tr').find('.approval_form_tbody_type').removeAttr("required");
			$input.closest('tr').find('.approval_form_tbody_type').attr({"required":true});
		}else{			
			$input.closest('tr').find('.approval_form_tbody_label').removeAttr("required");
			$input.closest('tr').find('.approval_form_tbody_type').removeAttr("required");
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

			$item.find(':input[name^=approval_form_path]').attr('name','approval_form_path['+(indForm)+'][]');
			$item.find(':input[name^=approval_form_key]').attr('name','approval_form_key['+(indForm)+'][]');
			$item.find(':input[name^=approval_form_title]').attr('name','approval_form_title['+(indForm)+'][]');

			$item.find(':input[name^=approval_form_tbody_label]').each(function(indAFD, elAFD){				
				var keyEL = $(elAFD).attr('name').match(/\[[a-zA-Z]+\]/)[0];
				$(elAFD).attr('name','approval_form_tbody_label['+(indForm)+']'+keyEL+'[]');
			});

		});
	}
</script>
@endpush