
<?php echo CHtml::hiddenField('currentForm', 'QRPrintAll')?>
<meta name="viewport" content="initial-scale=1.0 , minimum-scale=1.0 , maximum-scale=1.0" />

<table class="" id="qrprint_List" border="0">
    <thead>
        <tr> 
            <th style="text-align:center;"></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<style>
    @media print  {  
        /* div.table-responsive {page-break-inside:avoid;} */ 
        @page { 
            size: 91mm 52mm; 
            margin-top: 5mm;
            margin-left: 3.8mm;
            margin-right: 1mm;
            margin-bottom: 0mm;
        } 
    } 
    table{
        width: 92mm !important;  
    } 
    td{  
        width: 92mm !important; 
        height: 49.3mm !important;  
    } 
    div.cover_qrcode{
        height: 49.3mm !important;
        /* margin: 5px;   */
        width: 100%;  
        display: -webkit-flex;  
        display: flex;
    }
     
    div#qrcode_lbl{
        width: 47%;
        margin : 2%; 
    }

    div#qrcode_img{
        margin : 2%;   
    }

    div#qrcode_img img{            
        width: 100%;
        height: 100%;
        padding-top: 19px;
    }
    
    h4{
        margin-top: 15px;
    }

</style>
 