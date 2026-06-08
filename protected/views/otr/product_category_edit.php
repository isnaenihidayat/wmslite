
<div class="modal fade product_category_edit_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Edit Product Category")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="" class="frm" method="POST" action="<?php echo Yii::app()->createUrl("otr/product_category_update", array()) ?>">
                    <?php echo CHtml::hiddenField('id_edit','')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 top10"><?php echo t("Category Name");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('name_edit','',array(
                                    'placeholder'=>t("Category Name"),
                                    'required'=>true,
                                ))?>
                            </div>
                        </div>

                        
                        <div class="row top20">
                            <div class="col-md-5">
                                <button class="btn btn-primary" type="submit"><?php echo t("Update")?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>