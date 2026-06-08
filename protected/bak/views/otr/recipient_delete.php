
<div class="modal fade recipient_delete_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Delete Recipient")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="" class="frm" method="POST" action="<?php echo Yii::app()->createUrl("otr/recipient_delete", array()) ?>">
                    <?php echo CHtml::hiddenField('id_delete','')?>
                    <div class="inner">

                        <div class="row">
                            <p>Are you sure to delete <span id="name_delete"></span>?</p>
                            <hr>
                            <button class="btn btn-danger" type="submit"><?php echo t("Delete")?></button>
                        </div>

                    </div>
                </form>

            </div>

        </div>
    </div>
</div>