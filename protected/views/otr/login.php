<?php
/**
 * Created by IntelliJ IDEA.
 * User: isnaeni.hidayat
 * Date: 4/1/2017
 * Time: 11:11 AM
 */

?>

<div class="container" id="login-block">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
            <div class="account-wall text-center animated fadeInDown" style="margin-top: 30px;">
                <h1 class="logo-name" style="font-size: 100px;">
                    <img alt="image" src="<?php echo Yii::app()->baseUrl.'/assets/images/logo@2x.png'; ?>" />
                </h1>
                <h3 class="system-name">Welcome to <?php echo Functions::getCompanyName()?></h3>

                <div id="error"></div>

                <form class="form-signin" method="POST" id="login-form" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','login')?>
                    <div class="append-icon m-b-10">
                        <input type="text" name="email_address" id="email_address" class="form-control form-white email_address" placeholder="Email" required>
                        <i class="icon-user"></i>
                    </div>
                    <div class="append-icon m-b-20">
                        <input type="password" name="password" id="password" class="form-control form-white password" placeholder="Password" required>
                        <i class="icon-lock"></i>
                    </div>
                    <button class="btn btn-lg btn-danger btn-block" data-style="expand-left">
                        <?php echo Driver::t("Sign In")?> <i class="ion-ios-arrow-thin-right"></i>
                    </button>

                    <div class="clearfix">
                        <p class="pull-left m-t-20"><a id="formpassword" href="#">Forgot password?</a></p>
                    </div>
                </form>

                <!--form reset-->
                <form id="frm-forgotpass" class="form-password" role="form">
                    <div class="append-icon m-b-20">
                        <input type="email" name="email" class="form-control form-white email" placeholder="email" required>
                        <i class="icon-lock"></i>
                    </div>
                    <button type="submit" id="submit-password" class="btn btn-lg btn-danger btn-block" data-style="expand-left">Send Password Reset Link</button>
                    <div class="clearfix">
                        <p class="pull-left m-t-20"><a id="login" href="#">Already got an account? Sign In</a></p>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <p class="account-copyright">
        <span class="system-name">&copy; 2019 <a href="http://elog.id">PT eLogistik System Indonesia.</a></span> <br />
    </p>
</div>
