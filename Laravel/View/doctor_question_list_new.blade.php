@extends('layouts.innerlayout')
@section('content')
<script>
    $(document).ready(function()
    {
        $('#list-table').dataTable
        ({
            "bProcessing": true,
            "bServerSide": false,
            "iDisplayLength":10,
            "bPaginate": true,              
            "bAutoWidth": false,
            "iDisplayStart": 0,
            "bLengthChange": true,//for sorting 10,20,30,50 ....
            'sAjaxSource' : '<?php echo URL::to('doctor');?>/questionlist',
            "aaSorting": [[ 2, "desc" ]],
            "sPaginationType": "full_numbers",
            "aoColumns":[
                {"bSearchable": true,"bSortable": false,"sClass":"alignCenter"},
                {"bSearchable": true,"bSortable": false,"sClass":"alignCenter"},
                {"bSearchable": true,"bSortable": true,"sClass":"alignCenter"},                    
                {"bSearchable": true,"bSortable": false,"sClass":"alignCenter"},
                {"bSearchable": true,"bSortable": false,"sClass":"alignCenter"},
                {"bSearchable": true,"bSortable": false,"sClass":"alignCenter"},
                {"bSearchable": true,"bSortable": false,"sClass":"alignCenter linkdata"}
            ],
            "fnServerData": function ( sSource, aoData, fnCallback ) 
            {
                $.ajax( {
                    "dataType": "json",
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": fnCallback
                } );
            } 
        });
    });    
</script>

<?php 
    $user_data=Doctors::where('doctor_id','=',Session::get('doctor_id'))->first();
    $pic_file=$user_data['doctor_profile_picture_path'];
    $name=$user_data['doctor_profile_name'];
?>
<div class="container" >
    <ul class="breadcrumb"></ul>
</div>

<div class="container" >
    <div class="row-fluid">
        <div class="span12 " > 
            <div class="row-fluid">
                <div class="span2">
                    <div class="LeftNavWrapper">
                        @include('common.side_bar_navigation')
                    </div>
                </div>    
                <div class="span10">
                    <div class="questionhead">                            
                        <h1>Questions</h1>
                    </div>
                    <table class="table-list dataTable tablepadding" cellpadding="0" cellspacing="0" id="list-table">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Question</th>  
                                <th>Ask Time</th>                                                                        
                                <th>Agrees</th>
                                <th>Disagrees</th>
                                <th>Thanks</th>
                                <th>My Answer</th>
                            </tr>     
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop