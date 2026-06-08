<div class="PanelOverlay" style="display: block;"></div>
<div id="wrapper dashboard">
    <div id="map-wrapper" class="gray-bg">

        <div class="row border-bottom white-bg">
            <?php $this->renderPartial('/tpl/top_otr', array()); ?>
        </div>

        <div class="wrapper wrapper-content dashboard-work-area" id="ical">
            <div id="primary_map" class="primary_map"></div>
        </div>


    </div>
</div>

<div class='container popup' style="display: block;">
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Demo Movement") ?></h3>
            <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
        </div>
        <div class="panel-body" style="">
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-primary demo_movement_new" href="javascript:;">
                        <?php echo t("Add Demo Movement") ?>
                    </a>
                    <a class="btn btn-warning" href="<?php echo Yii::app()->createUrl("otr/demo_movement", array()) ?>">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <br>
            <table id="demo_movement_list" class="table table-striped table-bordered table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th><?php echo t("#") ?></th>
                        <th><?php echo t("Demo Req Number") ?></th>
                        <th><?php echo t("Requested By") ?></th>
                        <th><?php echo t("From Loc") ?></th>
                        <th><?php echo t("To Loc") ?></th>
                        <th><?php echo t("Status") ?></th>
                        <th><?php echo t("Created At") ?></th>
                        <th><?php echo t("Created By") ?></th>
                        <th style="width:150px;text-align:center;"><?php echo Driver::t("Action") ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>


<div class="footer fixed" id="footer">
    <div>
        <strong>Copyright</strong> <a href="http://elog.id/" target="_blank">eLogistik System Indonesia &copy; <?php echo date("Y") ?></a>
    </div>
</div>

<?php
$this->renderPartial('/otr/demo_movement_new', array());;
$this->renderPartial('/otr/demo_movement_edit', array());;
$this->renderPartial('/otr/demo_movement_edit_return', array());;
$this->renderPartial('/otr/demo_movement_delete', array());;
$this->renderPartial('/otr/demo_movement_detail', array());;
$this->renderPartial('/otr/demo_movement_return', array());;

$url = Yii::app()->createUrl('/ajax/demo_movement_list');
$url2 = Yii::app()->createUrl('/ajax/demo_movement_list_detail');

$js = <<<JS
var dataTable;
var dataTableDetail;


$(document).ready(function() {
    dataTable =  $('#demo_movement_list').DataTable( {
        "scrollY": 400,
        "scrollX": true,
        "iDisplayLength": 20,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "$url",
        "aaSorting": [
            [0, "DESC"]
        ],
        "sPaginationType": "full_numbers",
        /*"bLengthChange": false,*/
        "oLanguage": {
            "sProcessing": "<p>Processing.. <i class=\"fa fa-spinner fa-spin\"></i></p>"
        },
        "oLanguage": {
            "sEmptyTable": js_lang.tablet_1,
            "sInfo": js_lang.tablet_2,
            "sInfoEmpty": js_lang.tablet_3,
            "sInfoFiltered": js_lang.tablet_4,
            "sInfoPostFix": "",
            "sInfoThousands": ",",
            "sLengthMenu": js_lang.tablet_5,
            "sLoadingRecords": js_lang.tablet_6,
            "sProcessing": "Processing.. <i class=\"fa fa-spinner fa-spin\"></i>",
            "sSearch": js_lang.tablet_8,
            "sZeroRecords": js_lang.tablet_9,
            "oPaginate": {
                "sFirst": js_lang.tablet_10,
                "sLast": js_lang.tablet_11,
                "sNext": js_lang.tablet_12,
                "sPrevious": js_lang.tablet_13
            },
            "oAria": {
                "sSortAscending": js_lang.tablet_14,
                "sSortDescending": js_lang.tablet_15
            }
        },
        "fnInitComplete": function (oSettings, json) { },
        "lengthMenu": [
            [20, 30, 50, -1],
            [20, 30, 50, "All"]
        ],
        columnDefs: [
            {
                targets: 1,
                className: 'text-center'
            }
        ],
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            { extend: 'copy' },
            { extend: 'csv', title: 'data' },
            /*{extend: 'excel', title: data},*/
            {
                extend: 'excel',
                title: 'data',
                exportOptions: {
                    modifier: {
                        order: 'index', // 'current', 'applied', 'index',  'original'
                        page: 'all', // 'all',     'current'
                        search: 'none' // 'none',    'applied', 'removed'
                    }
                }
            },
            { extend: 'pdf', title: 'data' },

            {
                extend: 'print',
                customize: function (win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ]
    } );

} );


$(document).on("click", ".demo_movement_new", function () {
    $("#id").val('');
    id = $(this).data('id');

    $("#id").val(id);
    $(".demo_movement_new_modal").modal('show');
});

$('.demo_movement_new_modal').on('hide.bs.modal', function (e) {
    $('#hawb_demo_movement').val(null).trigger('change');
    $("#tbloutdemo").empty();
    dataTable.ajax.reload();
});

$('.demo_movement_edit_modal').on('hide.bs.modal', function (e) {
    $('#hawb_demo_movement_edit').val(null).trigger('change');
    $("#tbloutdemoedit").empty();
    dataTable.ajax.reload();
});

$('.demo_movement_return_modal').on('hide.bs.modal', function (e) {
    $("#tbloutdemoreturn").empty();
    dataTable.ajax.reload();
});

$('.demo_movement_return_edit_modal').on('hide.bs.modal', function (e) {
    $("#tbloutdemoreturnedit").empty();
    dataTable.ajax.reload();
});

$('.demo_movement_detail_modal').on('hide.bs.modal', function (e) {
    dataTableDetail.destroy();
});

if ($('#hawb_demo_movement').exists()) {
    $("#hawb_demo_movement").select2({
        dropdownParent: $('.demo_movement_new_modal'),
        ajax: {
            url: 'ajax/getHawbAddDemoMovement',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    idHawb: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });
}

if ($('#loc_demo_movement').exists()) {
    $("#loc_demo_movement").select2({
        dropdownParent: $('.demo_movement_new_modal'),
        ajax: {
            url: ajax_url + '/getLoc',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    idHawb: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });
}

if ($('#loc_demo_movement_edit').exists()) {
    $("#loc_demo_movement_edit").select2({
        dropdownParent: $('.demo_movement_edit_modal'),
        ajax: {
            url: ajax_url + '/getLoc',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    idHawb: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });
}

if ($('#hawb_demo_movement_edit').exists()) {
    $("#hawb_demo_movement_edit").select2({
        dropdownParent: $('.demo_movement_edit_modal'),
        ajax: {
            url: 'ajax/getHawbAddDemoMovement',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    idHawb: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });
}

$(document).on("click", ".demo_movement_edit", function (e) {
    var id = $(this).data('id');
    console.log(id);
    $.ajax({
        url: 'otr/demo_movement_get_with_detail',
        method: 'POST',
        data: 'id='+id,
        success: function(data){
            console.log(data);
            result = JSON.parse(data);


            $("#id_edit").val(id);
            $("#ref_edit").val(result.demo.ref);
            $("#requested_by_edit").val(result.demo.requested_by);
            $("#to_loc_edit").val(result.demo.to_loc);

            
            $("#loc_demo_movement_edit").empty() //empty select
            .append($("<option/>") //add option tag in select
                .val(result.demo.to_loc) //set value for option to post it
                .text(result.demo.to_loc)) //set a text for show in select
            .val(result.demo.to_loc) //select option of select2
            .trigger("change"); //apply to select2

            result.demo_detail.forEach(function(o){
                var html = '<tr class="item-row">';
                    html += '<td><input type="hidden" value="' + o.hawb + '" id="hawb_edit[]" name="hawb_edit[]">' + o.hawb + '</td>';
                    html += '<td><input type="hidden" value="' + o.loc + '" id="from_loc_edit[]" name="from_loc_edit[]">' + o.loc + '</td>';
                    html += '<td><a class="delinvdemo" href="javascript:;" title="Remove row">X</a></td></tr>';
                    $('#tblDetailDemoEdit > tbody:last-child').append(html);
            });
            
            
        }
    });

    $(".demo_movement_edit_modal").modal('show');
});

$(document).on("click", ".demo_movement_edit_return", function (e) {
    var id = $(this).data('id');
    console.log(id);
    $.ajax({
        url: 'otr/demo_movement_get_with_detail',
        method: 'POST',
        data: 'id='+id,
        success: function(data){
            console.log(data);
            result = JSON.parse(data);


            $("#id_edit_return").val(id);
            $("#ref_return_edit").val(result.demo.ref);
            $("#requested_by_return_edit").val(result.demo.requested_by);
            $("#from_loc_return_edit").val(result.demo.from_loc);

            result.demo_detail.forEach(function(o){
                var html = '<tr class="item-row">';
                    html += '<td><input type="hidden" value="' + o.hawb + '" id="hawb_edit_return[]" name="hawb_edit_return[]">' + o.hawb + '</td>';
                    //html += '<td><input type="hidden" value="' + o.loc + '" id="to_loc_edit_return[]" name="to_loc_edit_return[]">' + o.loc + '</td>';
                    if(o.loc){
                        html += '<td><select class="to_loc_edit_return form-control" style="width:100%;" name="to_loc_edit_return[]"><option value="'+o.loc+'">'+o.loc+'</option></select></td></tr>';
                    }else{
                        html += '<td><select class="to_loc_edit_return form-control" style="width:100%;" name="to_loc_edit_return[]"><option>- Choose Return Location -</option></select></td></tr>';
                    }
                    $('#tblDetailDemoReturnEdit > tbody:last-child').append(html);

                $('.to_loc_edit_return:last').select2({
                    dropdownParent: $('.demo_movement_return_edit_modal'),
                    ajax: {
                        url: ajax_url + '/getLoc',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                idHawb: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
         
                });
            });
            
            
        }
    });

    $(".demo_movement_return_edit_modal").modal('show');
});

$(document).on("click", ".demo_movement_return", function (e) {
    var id = $(this).data('id');
    console.log(id);
    $.ajax({
        url: 'otr/demo_movement_get_with_detail',
        method: 'POST',
        data: 'id='+id,
        success: function(data){
            console.log(data);
            result = JSON.parse(data);


            $("#id_return").val(id);
            $("#ref_return").val(result.demo.ref);
            $("#requested_by_return").val(result.demo.requested_by);
            $("#from_loc_return").val(result.demo.to_loc);

            result.demo_detail.forEach(function(o){
                var html = '<tr class="item-row">';
                    html += '<td><input type="hidden" value="' + o.hawb + '" id="hawb_return[]" name="hawb_return[]">' + o.hawb + '</td>';
                    html += '<td><select class="to_loc_return form-control" style="width:100%;" name="to_loc_return[]"><option>- Choose Return Location -</option></select></td></tr>';
                    $('#tblDetailDemoReturn > tbody:last-child').append(html);

                $('.to_loc_return:last').select2({
                    dropdownParent: $('.demo_movement_return_modal'),
                    ajax: {
                        url: ajax_url + '/getLoc',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                idHawb: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
         
                });

            });
            
            
        }
    });

    $(".demo_movement_return_modal").modal('show');
    if ($('.to_loc_return').exists()) {
        $('.to_loc_return').select2({
            dropdownParent: $('.demo_movement_return_modal'),
            ajax: {
                url: ajax_url + '/getLoc',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        idHawb: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });
    }
});



$(document).on("click", ".demo_movement_delete", function (e) {
    var id = $(this).data('id');
    $.ajax({
        url: 'otr/demo_movement_get',
        method: 'POST',
        data: 'id='+id,
        success: function(data){
            result = JSON.parse(data);
            $("#id_delete").val(id);
            $("#name_delete").text(result.ref);
        }
    });

    $(".demo_movement_delete_modal").modal('show');
});

$(document).on("click", ".demo_movement_detail", function (e) {
    var id = $(this).data('id');
    
    dataTableDetail =  $('#demo_movement_list_detail').DataTable( {
        "iDisplayLength": 20,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "$url2" + "?id=" + id,
        "aaSorting": [
            [0, "DESC"]
        ],
        
        "lengthMenu": [
            [20, 30, 50, -1],
            [20, 30, 50, "All"]
        ],
        columnDefs: [
            {
                targets: 1,
                className: 'text-center'
            }
        ],
    } );

    $(".demo_movement_detail_modal").modal('show');
});



JS;


$cs = Yii::app()->getClientScript();
$cs->registerScript(
    'demo_movement_js',
    $js,
    CClientScript::POS_END
);

?>


<?php
if (Yii::app()->user->hasFlash('success')) {
    $success = Yii::app()->user->getFlash('success');

    $js = <<<JS
    setTimeout(function () {
    var title = 'Success';
    toastr.options = {
        closeButton: true,
        debug: false,
        progressBar: true,
        preventDuplicates: false,
        positionClass: 'toast-top-center',
        onclick: null,
        showDuration: 400,
        hideDuration: 2500,
        timeOut: 2000,
        extendedTimeOut: 500,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };
    toastr['success']('$success', title);
}, 100);
JS;

    $cs = Yii::app()->getClientScript();
    $cs->registerScript(
        'demo_movement_js_toast_success',
        $js,
        CClientScript::POS_END
    );
}

?>

<?php
if (Yii::app()->user->hasFlash('error')) {
    $error = Yii::app()->user->getFlash('error');

    $js = <<<JS
    setTimeout(function () {
    var title = 'Error';
    toastr.options = {
        closeButton: true,
        debug: false,
        progressBar: true,
        preventDuplicates: false,
        positionClass: 'toast-top-center',
        onclick: null,
        showDuration: 400,
        hideDuration: 2500,
        timeOut: 2000,
        extendedTimeOut: 500,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };
    toastr['error']('$error', title);
}, 100);
JS;

    $cs = Yii::app()->getClientScript();
    $cs->registerScript(
        'demo_movement_js_toast_error',
        $js,
        CClientScript::POS_END
    );
}

?>