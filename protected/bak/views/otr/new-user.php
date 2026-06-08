
<div class="modal fade new-user-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add User")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmUser" class="frmUser frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','addUser')?>
                    <?php echo CHtml::hiddenField('idUser','')?>
                    <?php echo CHtml::hiddenField('module','1')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 "><?php echo t("First Name");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('first_name','',array(
                                    'placeholder'=>t("First Name"),
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Last Name");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('last_name','',array(
                                    'placeholder'=>t("Last Name")
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Phone");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('phone','',array(
                                    'class'=>"mobile_inputs",
                                    'maxlength'=>15
                                ))?>
                            </div>
                        </div>

                        <?php //jika bukan admin, maka pilihan dropdown yg muncul, hanya yg sesuai dg tipe user tsb ?>
                        <?php if($_SESSION['wmslite']['admin'] == '1'): ?>
                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("is Admin?");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::dropDownList('admin','',array('' => '- is Admin?', '1' => 'Yes', '0' => 'No'),array(
                                    'required'=>true
                                ))?>
                            </div>
                        </div>
                        <?php endif ?>

                        <?php if($_SESSION['wmslite']['admin'] == '1'): ?>
                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Role");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::dropDownList('type','',
                                array(
                                    '0' => '- Choose Role -',
                                    '1' => 'Warehouse',
                                    '2' => 'Custom',
                                    '3' => 'Read Only',
                                ),array(
                                    'required'=>false
                                ))?>
                            </div>
                        </div>
                        <?php endif ?>

                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Email Address");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('email','',array(
                                    'placeholder'=>t("Email"),
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Password");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::passwordField('password','',array(
                                    'class'=>"validate",
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top20">
                            <div class="col-md-5">
                                <button class="btn btn-primary" type="submit"><?php echo t("Submit")?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
