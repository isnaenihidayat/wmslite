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
            <h3 class="panel-title"><?php echo t("Product Category") ?></h3>
            <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-primary product_category_new" href="javascript:;">
                        <?php echo t("Add Product Category") ?>
                    </a>
                    <a class="btn btn-warning" href="<?php echo Yii::app()->createUrl("otr/product_category", array()) ?>">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <br>
            <table id="product_category_list" class="table table-striped table-bordered table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th><?php echo t("Name") ?></th>
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
$this->renderPartial('/otr/product_category_new', array());;
$this->renderPartial('/otr/product_category_edit', array());;
$this->renderPartial('/otr/product_category_delete', array());;

$url = Yii::app()->createUrl('/ajax/product_category_list');

$js = <<<JS
$(document).ready(function() {
    var dataTable =  $('#product_category_list').DataTable( {
        "scrollY": 200,
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


$(document).on("click", ".product_category_new", function () {
    $("#id").val('');
    id = $(this).data('id');

    $("#id").val(id);
    $(".product_category_new_modal").modal('show');
});

$(document).on("click", ".product_category_edit", function (e) {
    var id = $(this).data('id');
    $.ajax({
        url: 'otr/product_category_get',
        method: 'POST',
        data: 'id='+id,
        success: function(data){
            result = JSON.parse(data);
            $("#id_edit").val(id);
            $("#name_edit").val(result.name);
        }
    });

    $(".product_category_edit_modal").modal('show');
});

$(document).on("click", ".product_category_delete", function (e) {
    var id = $(this).data('id');
    $.ajax({
        url: 'otr/product_category_get',
        method: 'POST',
        data: 'id='+id,
        success: function(data){
            result = JSON.parse(data);
            $("#id_delete").val(id);
            $("#name_delete").text(result.name);
        }
    });

    $(".product_category_delete_modal").modal('show');
});



JS;


$cs = Yii::app()->getClientScript();
$cs->registerScript(
    'product_category_js',
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
        'product_category_js_toast_success',
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
        'product_category_js_toast_error',
        $js,
        CClientScript::POS_END
    );
}

?>