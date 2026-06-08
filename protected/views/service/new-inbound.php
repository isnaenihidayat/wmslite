
<div class="modal fade new-in-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Inbound")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmIn" class="frm frmIn" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','addIns')?>
                    <?php echo CHtml::hiddenField('idInb','')?>
                    <?php echo CHtml::hiddenField('filename','')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("HAWB");?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('hawb','',array(
                                    'placeholder'=>t("HAWB"),
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("PO Number");?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('po_number','',array(
                                    'placeholder'=>t("PO Number")
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Number of Coli");?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('locator_number','',array(
                                    'placeholder'=>t("Number of Coli")
                                ))?>
                            </div>
                        </div>

                        <div class="frmDetail">
                            <div class="hr-line-dashed"></div>

                            <div class="row">
                                <div class="col-md-3">
                                    <?php echo t("SO #");?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo t("Part #");?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo t("Description");?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo t("Qty");?>
                                </div>
                            </div>

                            <div class="row top10 frmDetail">
                                <div class="col-md-3">
                                    <?php echo CHtml::textField('so_number','',array(
                                        'placeholder'=>t("SO Number"),
                                        'class'=>"sonumber",
                                    ))?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo CHtml::textField('part_number','',array(
                                        'placeholder'=>t("Part Number"),
                                        'class'=>"partnumber",
                                    ))?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo CHtml::textField('descr_part','',array(
                                        'placeholder'=>t("Description")
                                    ))?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo CHtml::textField('qty','',array(
                                        'placeholder'=>t("Qty")
                                    ))?>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-primary addInDown" type="button"><?php echo t("+")?></button>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="table-responsive">
                                    <table class="table table-hover issue-tracker" id="tblsDetail">
                                        <thead>
                                            <tr>
                                                <th ><?php echo t("SO #")?></th>
                                                <th ><?php echo t("Part #")?></th>
                                                <th ><?php echo t("Descr#")?></th>
                                                <th ><?php echo t("Qty#")?></th>
                                                <th ><?php echo t("Action")?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblsIn"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row top20">
                            <div class="col-md-8">
                                <button class="btn btn-primary" type="submit" id="submitIn" disabled><?php echo t("Submit")?></button>
                                <a id="upload-doc" class="btn btn-warning"><?php echo t("Upload Document")?></a>
                                <div id="progressBar"></div>
                                <div id="progressOuter"></div>
                                <div id="msgBox"></div>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>