<script>
    data = 'shipment_list';
    var params = $("#shipment_list #frm_table").serialize();
    data_table = $('#shipment_list').dataTable({
        // "scrollY": 200,
        // "scrollX": false,
        fixedHeader: true,
        "iDisplayLength": 10,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= Yii::app()->getBaseUrl(true) ?>/ajax/shlist/?currentController=admin&" + params,
        "aaSorting": [
            [0, "DESC"]
        ],
        "sPaginationType": "full_numbers",
        /*"bLengthChange": false,*/
        "oLanguage": {
            "sProcessing": "<p>Processing.. <i class=\"fa fa-spinner fa-spin\"></i></p>"
        },

        "fnInitComplete": function(oSettings, json) {},
        "lengthMenu": [
            [10, 20, 30, 50, -1],
            [10, 20, 30, 50, "All"]
        ],
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [{
                extend: 'copy'
            },
            {
                extend: 'csv',
                title: data
            },
            /*{extend: 'excel', title: data},*/
            {
                extend: 'excel',
                title: data,
                exportOptions: {
                    modifier: {
                        order: 'index', // 'current', 'applied', 'index',  'original'
                        page: 'all', // 'all',     'current'
                        search: 'none' // 'none',    'applied', 'removed'
                    }
                }
            },
            {
                extend: 'pdf',
                title: data
            },

            {
                extend: 'print',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ]
    });
</script>