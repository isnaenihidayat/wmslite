<?php if (Yii::app()->user->hasFlash('success')) : ?>
    <div class="alert alert-success alert-dismissible">
        <?php echo Yii::app()->user->getFlash('success'); ?>
        <button data-dismiss="alert" type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>


<?php if (Yii::app()->user->hasFlash('danger')) : ?>
    <div class="alert alert-danger alert-dismissible">
        <?php echo Yii::app()->user->getFlash('danger'); ?>
        <button data-dismiss="alert" type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>