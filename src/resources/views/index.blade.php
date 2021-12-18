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
                            <table id="approval-table" class="table table-striped" style="width:100%;"></table>
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
        var approvalDataTable = null;
        $(document).ready(function(){            
            approvalDataTable = $('#approval-table').DataTable({
            dom: '<"row"<"col-12 col-sm-6"B><"col-12 col-sm-6"lf>><"row"<"col-12 col-sm-12"t><"col-12 col-sm-6"i><"col-12 col-sm-6"p>>',
            lengthMenu: [[5, 10, 20, 50, -1], [5, 10, 20, 50, "All"]],
            buttons: [
                {
                    text: 'Add New',
                    attr: {
                        class: 'btn btn-success btn-sm'
                    },
                    action: function ( e, dt, node, config ) {
                        window.open("{{route('approvals.create')}}","_self");
                    }
                }
            ],
            columns: [
                {
                    'title': '#SL', data: 'id', class: "no-sort", width: '50px', render: function (data, row, type, col) {
                        var pageInfo = approvalDataTable.page.info();
                        return (col.row + 1) + pageInfo.start;
                    }
                },
                {'title': 'Title', name: 'title', data: "title"},
                {'title': 'Create', name: 'on_create', data: "on_create",render: function(data){
                    return (data) ? 'Yes' : 'No';
                }},
                {'title': 'Update', name: 'on_update', data: "on_update",render: function(data){
                    return (data) ? 'Yes' : 'No';
                }},
                {'title': 'Delete', name: 'on_delete', data: "on_delete",render: function(data){
                    return (data) ? 'Yes' : 'No';
                }},
                {'title': 'Swap Enable', name: 'do_swap', data: "do_swap",render: function(data){
                    return (data) ? 'Yes' : 'No';
                }},
                {'title': 'Status', name: 'status', data: "status",render: function(data){
                    return (data) ? 'Enable' : 'Disable';
                }},
                {
                    'title': 'Option', data: 'id', class: 'text-right width-5-per', render: function (data, type, row, col) {
                        let returnData = '';
                        let editRoute = "{{route('approvals.edit','ITEM_ID')}}";
                        returnData += '<a href="'+editRoute.replace('ITEM_ID',data)+'" class="btn btn-sm btn-primary text-white text-center"><i class="far fa-edit"></i></a> ';
                        returnData += '<a href="javascript:void(0);" data-val="'+data+'" class="btn btn-sm btn-info text-white text-center changeStatus"><i class="fa fa-eye-slash"></i></a> ';
                        returnData += '<a href="javascript:void(0);" data-val="'+data+'" class="btn btn-sm btn-danger text-white text-center deleteEvent"><i class="fa-times fas"></i></a>';

                        return returnData;
                    }
                },
            ],

            ajax: {
                url: "{{route('approvals.index')}}",

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
                targets: [0,2,3,4,5]
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
                    approvalDataTable.rows()
                    .invalidate()
                    .draw();
                });
            }
        });

        $(document).on('click', '.deleteEvent', function () {
            var $el = $(this);
            if(confirm("Are you sure you want to delete?")){                
                $.ajax({
                    'type' : 'POST',
                    'data' : {'_method' : 'delete', '_token' : $('meta[name=csrf-token]').attr('content')},
                    'url' : '{{route('approvals.destroy',['approval' => 'ITEM_ID'])}}'.replace("ITEM_ID",$el.attr('data-val'))
                }).done(function(){
                    approvalDataTable.rows()
                    .invalidate()
                    .draw();
                });
            }
        });
    </script>
@endpush
