{use class="common\helpers\Html"}
<!DOCTYPE html>
<html lang="{str_replace("_", "-", Yii::$app->language)}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{$this->title}</title>
    <link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
    <link href="{$app->view->theme->baseUrl}/css/login.css?3" rel="stylesheet" type="text/css" />
    <link href="{$app->view->theme->baseUrl}/css/superadmin.css?3" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery.min.js"></script>
</head>
<body>
<div class="login">
    <div class="content-login">
        <!-- Logo -->
        <div class="logo">
            {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
                <img src="{$app->view->theme->baseUrl}/img/logo-powerful-commerce-white.png" alt="Powerful">
                <div class="subLogo">SUPERADMIN AREA</div>
            {else}
                {include '../login/login-logo.tpl'}
            {/if}
        </div>
        <!-- /Logo -->

        <!-- Login Box -->
        <div class="box">
            <div class="content">
{Html::beginForm($formAction, 'post')}
    {Html::hiddenInput('hash', $alslHash)}

    <h3 class="form-title">{$smarty.const.TEXT_CONFIRM_PROCESS_LOGIN_LINK}</h3>

    {Html::submitButton('Confirm', ['class' => 'btn btn-primary btn-block'])}
{Html::endForm()}
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
                    <li><i class="icon-envelope"></i> <a href="http://www.holbi.co.uk/contact-us" target="_blank">{$smarty.const.TEXT_HEADER_CONTACT_US}</a></li>
                {/if}
                {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                    {if ((defined('WL_SERVICES_URL') && WL_SERVICES_URL === true) &&
                    (defined('WL_SERVICES_TEXT') && WL_SERVICES_TEXT != '') &&
                    (defined('WL_SERVICES_WWW') && WL_SERVICES_WWW != ''))}
                        <li><a href="{$smarty.const.WL_SERVICES_WWW}" target="_blank">{$smarty.const.WL_SERVICES_TEXT}</a></li>
                    {/if}
                {else}
                    <li><i class="icon-shopping-cart"></i> <a href="http://www.holbi.co.uk/ecommerce-development" target="_blank">{$smarty.const.TEXT_ECOMMERCE_DEVELOPMENT}</a></li>
                {/if}
                {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                    {if ((defined('WL_SUPPORT_URL') && WL_SUPPORT_URL === true) &&
                    (defined('WL_SUPPORT_TEXT') && WL_SUPPORT_TEXT != '') &&
                    (defined('WL_SUPPORT_WWW') && WL_SUPPORT_WWW != ''))}
                        <li><a href="{$smarty.const.WL_SUPPORT_WWW}" target="_blank">{$smarty.const.WL_SUPPORT_TEXT}</a></li>
                    {/if}
                {else}
                    <li><i class="icon-comments"></i> <a href="http://www.holbi.co.uk/ecommerce-support" target="_blank">{$smarty.const.TEXT_SUPPORT}</a></li>
                {/if}
            </ul>

            {if ((defined('WL_ENABLED') && WL_ENABLED === true) &&
            (defined('WL_COMPANY_NAME') && WL_COMPANY_NAME != ''))}

                Copyright &copy; {$smarty.now|date_format:"%Y"} <a target="_blank" href="http://loadedcommerce.com">{$smarty.const.WL_COMPANY_NAME}</a>. All rights reserved.

            {else}


                <div class="copuright">
                    {$smarty.const.TEXT_COPYRIGHT} {$smarty.now|date_format:"%Y"} <a target="_blank" href="http://www.holbi.co.uk">{$smarty.const.TEXT_COPYRIGHT_HOLBI}</a>
                    {$smarty.const.TEXT_FOOTER_BOTTOM}<br>

                    {$smarty.const.TEXT_FOOTER_COPYRIGHT} {$smarty.now|date_format:"%Y"} {$smarty.const.TEXT_COPYRIGHT_HOLBI}
                </div>
            {/if}

        </div>
        <!-- /Footer -->
    </div>
</body>
</html>
