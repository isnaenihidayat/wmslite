<?php $this->renderPartial('/layouts/header');?>

<body class="<?php echo isset($this->body_class)?$this->body_class:'';?>" <?php echo isset($this->body_page)?$this->body_page:'' ?>>

<?php echo $content;?>

<div class="main-preloader">
    <div class="loader">
        <div class="loader__figure"></div>
        <p class="loader__label">eLog.ID</p>
    </div>
</div>
<?php $this->renderPartial($this->script);?>
</body>
<?php $this->renderPartial('/layouts/footer');?>
