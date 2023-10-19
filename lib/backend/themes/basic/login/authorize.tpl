<!DOCTYPE html>
<html lang="{str_replace("_", "-", Yii::$app->language)}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{$this->title}</title>
        <!-- Bootstrap -->
        <link href="{$app->request->baseUrl}/plugins/bootstrap/bootstrap.min.css" rel="stylesheet" type="text/css" />

        <!-- Theme -->
        <link href="{$app->view->theme->baseUrl}/css/main.css" rel="stylesheet" type="text/css" />
        <link href="{$app->view->theme->baseUrl}/css/responsive.css" rel="stylesheet" type="text/css" />

        <!-- Login -->
        <link href="{$app->view->theme->baseUrl}/css/login.css" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>

        <link href="{$app->request->baseUrl}/plugins/bootstrap-switch/bootstrap-switch.css" rel="stylesheet" />

        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery.min.js"></script>
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/bootstrap/bootstrap.min.js"></script>
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/bootstrap-switch/bootstrap-switch.js"></script>
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/lodash.compat.min.js"></script>

        <!-- Bootstrap password -->
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/bootstrap-show-password.min.js"></script>

        <!-- Beautiful Checkboxes -->
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/uniform/jquery.uniform.min.js"></script>

        <!-- Form Validation -->
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/validation/jquery.validate.min.js"></script>

        <!-- Slim Progress Bars -->
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/nprogress/nprogress.js"></script>

        <!-- App -->
        <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/login.js"></script>
        <script>
        $(document).ready(function() {
            "use strict";
            Login.init(); // Init login JavaScript
        });
        </script>
    </head>
{use class="yii\helpers\Html"}

    <body class="">
    <div class="login">
        <div class="content-login">
            <!-- Logo -->
            <div class="logo">
                {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
                    <img src="{$app->view->theme->baseUrl}/img/{$smarty.const.WL_COMPANY_LOGO}" alt="{$smarty.const.WL_COMPANY_NAME}" />
                {else}
                    <img src="{$app->view->theme->baseUrl}/img/tl-logo.png" alt="logo" />
                {/if}
            </div>
            <!-- /Logo -->

            <!-- Login Box -->
            <div class="box">
                <div class="content">
                    <!-- Login Form -->
                    {Html::beginForm($app->urlManager->createUrl(["login", 'action' => 'process']), 'post', ['class' => 'form-vertical login-form'])}
                        <!-- Title -->
                        <h3 class="form-title">{$smarty.const.TEXT_SIGN_IN_ACCOUNT}</h3>
                        <input type="hidden" value="authorize" name="action" />
                        {if $isMobile}
                            <div class="alert fade in alert-warning">
                                <i class="icon-remove close" data-dismiss="alert"></i>
                                {$smarty.const.TEXT_SECURITY_KEY_MOBILE_DISCLAIMER}
                            </div>
                        {/if}
                        <div class="alert fade in alert-info">
                            <i class="icon-remove close" data-dismiss="alert"></i>
                            {if $type == 'sms'}
                                {$smarty.const.TEXT_SECURITY_KEY_VIA_PHONE}
                            {else}
                                {$smarty.const.TEXT_SECURITY_KEY_VIA_EMAIL}
                            {/if}
                        </div>
                        <!-- Error Message -->
                        <div class="alert fade in alert-danger" style="display: none;">
                            <i class="icon-remove close" data-dismiss="alert"></i>
                            {$smarty.const.TEXT_ENTER_SECURITY_KEY}
                        </div>
                        <!-- Input Fields -->
                        <div class="form-group">
                            <div class="input-icon">
                                <i class="icon-lock"></i>
                                <input type="text" name="security_key" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_SECURITY_KEY}" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_SECURITY_KEY}" />
                            </div>
                        </div>
                    <div class="row">
                        <div class="col-6 ">
                            <div class="form-group">
                                <label>{$smarty.const.TEXT_SECURITY_IS_GUEST}</label>
                                {tep_draw_checkbox_field('ad_is_guest', '1', '1')}
                            </div>
                        </div>
                        <div class="col-6" style="padding-top: 4px">
                            <span class="btn" onclick="window.location.reload()">{$smarty.const.RESEND_CODE}</span>
                        </div>
                    </div>
                        <script type="text/javascript">
                            $(function() {
                                $('input[name="security_key"]').password();
                                $('input[type="checkbox"]').bootstrapSwitch( {
                                    onText: "{$smarty.const.SW_ON}",
                                    offText: "{$smarty.const.SW_OFF}",
                                    handleWidth: '20px',
                                    labelWidth: '24px',
                                    onSwitchChange: function (event, state) {
                                        $('#holder_security_key_expire').hide();
                                        if (state == false) {
                                            $('#holder_security_key_expire').show();
                                        }
                                        return true;
                                    }
                                } );
                            });

                        </script>
                        <div id="holder_security_key_expire" class="form-group" style="display: none;">
                            <label>{$smarty.const.TEXT_SECURITY_KEY_EXPIRE}</label>
                            <div class="input-icon">
                                {tep_draw_pull_down_menu('security_key_expire', $securityKeyExpireArray, '', 'class="form-control"')}
                            </div>
                        </div>
                        <!-- /Input Fields -->

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="submit btn btn-primary">
                                {$smarty.const.TEXT_SIGN_IN} <i class="icon-angle-right"></i>
                            </button>
                        </div>
                    {Html::endForm()}
                    <!-- /Login Form -->
                </div> <!-- /.content -->
            </div>
            <!-- /Login Box -->
        </div>

        <!-- Footer -->
        <div class="footer-login">
            <ul class="links">
                {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                    {if ((defined('WL_CONTACT_URL') && WL_CONTACT_URL === true) &&
                    (defined('WL_CONTACT_TEXT') && WL_CONTACT_TEXT != '') &&
                    (defined('WL_CONTACT_WWW') && WL_CONTACT_WWW != ''))}
                        <li><a href="{$smarty.const.WL_CONTACT_WWW}" target="_blank">{$smarty.const.WL_CONTACT_TEXT}</a></li>
                    {/if}
                {else}
                    <li><i class="icon-envelope"></i> <a href="https://www.oscommerce.com/contact" target="_blank">{$smarty.const.TEXT_HEADER_CONTACT_US}</a></li>
                {/if}
                {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                    {if ((defined('WL_SERVICES_URL') && WL_SERVICES_URL === true) &&
                    (defined('WL_SERVICES_TEXT') && WL_SERVICES_TEXT != '') &&
                    (defined('WL_SERVICES_WWW') && WL_SERVICES_WWW != ''))}
                        <li><a href="{$smarty.const.WL_SERVICES_WWW}" target="_blank">{$smarty.const.WL_SERVICES_TEXT}</a></li>
                    {/if}
                {else}
                    <li><i class="icon-comments"></i> <a href="https://www.holbi.co.uk/ecommerce-development" target="_blank">{$smarty.const.TEXT_ECOMMERCE_DEVELOPMENT}</a></li>
                {/if}
                {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                    {if ((defined('WL_SUPPORT_URL') && WL_SUPPORT_URL === true) &&
                    (defined('WL_SUPPORT_TEXT') && WL_SUPPORT_TEXT != '') &&
                    (defined('WL_SUPPORT_WWW') && WL_SUPPORT_WWW != ''))}
                        <li><a href="{$smarty.const.WL_SUPPORT_WWW}" target="_blank">{$smarty.const.WL_SUPPORT_TEXT}</a></li>
                    {/if}
                {else}
                    <li><i class="icon-shopping-cart"></i> <a href="https://forums.oscommerce.com/" target="_blank">{$smarty.const.TEXT_SUPPORT}</a></li>
                {/if}
            </ul>

            {if ((defined('WL_ENABLED') && WL_ENABLED === true) &&
            (defined('WL_COMPANY_NAME') && WL_COMPANY_NAME != ''))}

                Copyright &copy; {$smarty.now|date_format:"%Y"} <a target="_blank" href="https://oscommerce.com">{$smarty.const.WL_COMPANY_NAME}</a>. All rights reserved.

            {else}


                <div class="copuright">
                    {$smarty.const.TEXT_COPYRIGHT} {$smarty.now|date_format:"%Y"} <a target="_blank" href="https://www.oscommerce.com">{$smarty.const.TEXT_COPYRIGHT_HOLBI}</a>
                    {$smarty.const.TEXT_FOOTER_BOTTOM}<br>

                    {$smarty.const.TEXT_FOOTER_COPYRIGHT} {$smarty.now|date_format:"%Y"} {$smarty.const.TEXT_COPYRIGHT_HOLBI}
                </div>
            {/if}

        </div>
        <!-- /Footer -->
    </div>
    </body>


</html>