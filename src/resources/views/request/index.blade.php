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
							<h5>{{$approval->title}} List</h5>
						</div>
						<div class="col-12">
							<table id="approval-request-table" class="table table-striped" style="width:100%;"></table>
						</div>
					</div>
				</div>
			</div>
		</div>
@endsection
@push(config('approval-config.script-stack'))
	@if(config('approval-config.load-script'))
	@include('laravel-approval::partials.script')
	@endif
	<script type="text/javascript">
		var approvalRequestDataTable = null;
		var dataField = {!!json_encode($approval->list_data_fields)!!};
		var approval = {!!json_encode($approval)!!};
		$(document).ready(function(){            
			approvalRequestDataTable = $('#approval-request-table').DataTable({
			dom: '<"row"<"col-12 col-sm-6"Bl><"col-12 col-sm-6"f>><"row"<"col-12 col-sm-12"t><"col-12 col-sm-6"i><"col-12 col-sm-6"p>>',
			lengthMenu: [[5, 10, 20, 50, -1], [5, 10, 20, 50, "All"]],
			buttons: [],
			columns: [
				{
					'title': '#SL', data: 'id', class: "no-sort", width: '50px', render: function (data, row, type, col) {
						var pageInfo = approvalRequestDataTable.page.info();
						return (col.row + 1) + pageInfo.start;
					}
				},
				{'title': 'Title', name: 'title', data: "id", render : function(data, type, row){
					var htmlData = '';
					$.each(row.approvable,function(infA, valA){
						if(dataField.indexOf(infA) != -1){
						   htmlData += valA+'<br>'; 
						}
					})
					return htmlData;
				}},
				{'title': 'Level', name: 'level', data: "approvable",render : function(data, type, row){
					var htmlData = row.completed ? 'Completed' : 'Pending';
					$.each(approval.levels,function(infAL, valAL){
						var status_fields = valAL.status_fields;
						var matched = true;
						for(ind in status_fields.pending){
							if(data[ind] != status_fields.pending[ind]){
								matched = false;
								break;
							}
						}
						if(matched){
							htmlData = valAL.title;
							return false;
						}
					})
					return htmlData;
				}},
				{
					'title': 'Option', data: 'id', class: 'text-right width-5-per', render: function (data, type, row, col) {
						let returnData = '';
						let editRoute = "{{route('approval_request.show','ITEM_ID')}}";
						returnData += '<a href="'+editRoute.replace('ITEM_ID',data)+'" class="btn btn-sm btn-primary text-white text-center"><i class="far fa-edit"></i></a> ';
						// returnData += '<a href="javascript:void(0);" data-val="'+data+'" class="btn btn-sm btn-info text-white text-center changeStatus"><i class="fa fa-eye-slash"></i></a> ';
						// returnData += '<a href="javascript:void(0);" data-val="'+data+'" class="btn btn-sm btn-danger text-white text-center deleteEvent"><i class="fa-times fas"></i></a>';

						return returnData;
					}
				},
			],

			ajax: {
				url: "{{route('approval_request.index',['approval' => $approval->id])}}",
			},

			language: {
				paginate: {
					next: '&#8594;', // or '→'
					previous: '&#8592;' // or '←'
				}
			},
			columnDefs: [{
				searchable: false,
				orderable: false,
				targets: [0,1,2,3]
			}],
			responsive: true,
			autoWidth: false,
			serverSide: true,
			processing: true,
			});
		});

		$(document).on('click', '.changeStatus', function () {
			var $el = $(this);
			if(confirm("Are you sure you want to change the status?")){
				$.ajax({
					'type' : 'GET',
					'url' : '{{route('approvals.change_status',['approval' => 'ITEM_ID'])}}'.replace("ITEM_ID",$el.attr('data-val'))
				}).done(function(){
					approvalRequestDataTable.rows()
					.invalidate()
					.draw();
				});
			}
		});

		$(document).on('click', '.deleteEvent', function () {
			var $el = $(this);
			if(confirm("Are you sure you want to delete?")){
				let deleteRoute = "{{route('approvals.destroy','ITEM_ID')}}".replace("ITEM_ID",$el.attr('data-val'));
			}
		});
	</script>
@endpush