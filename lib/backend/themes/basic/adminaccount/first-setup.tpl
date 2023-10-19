{use class="yii\helpers\Html"}
<!DOCTYPE html>
<html lang="{str_replace("_", "-", Yii::$app->language)}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin Account | Trueloaded Admin</title>
        <link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
        <link href="{$app->view->theme->baseUrl}/css/login.css?1" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery.min.js"></script>
        <script type="text/javascript" src="{$app->request->baseUrl}/plugins/validation/jquery.validate.min.js"></script>
        <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/login.js"></script>
    </head>
    <body>
        <div class="login">
            <div class="content-login">
                <!-- Logo -->
                <div class="logo">
                    {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
                        <img src="{$app->view->theme->baseUrl}/img/{$smarty.const.WL_COMPANY_LOGO}" alt="{$smarty.const.WL_COMPANY_NAME}" />
                    {else}
                        {include '../login/login-logo.tpl'}
                    {/if}
                </div>
                <!-- /Logo -->
                <div class="box">
                    <div class="content">
                        <h3 class="form-title">{$smarty.const.TEXT_UPDATE_IN_ACCOUNT}</h3>
                        {Html::beginForm($app->urlManager->createUrl(["adminaccount/first-setup", 'action' => 'process']), 'post', ['class' => 'form-vertical login-form'])}
                        <div class="main_row">
                            <div class="main_title">{$smarty.const.ENTRY_FIRST_NAME}</div>
                            <div class="main_value">{tep_draw_input_field('admin_firstname', '', 'maxlength="32" class="form-control"', false)}</div>
                        </div>
                        <div class="main_row">
                            <div class="main_title">{$smarty.const.ENTRY_LAST_NAME}</div>
                            <div class="main_value">{tep_draw_input_field('admin_lastname', '', 'maxlength="32" class="form-control"', false)}</div>
                        </div>

                        <div class="main_row">
                            <div class="main_title">{$smarty.const.ENTRY_EMAIL_ADDRESS}</div>
                            <div class="main_value">{tep_draw_input_field('admin_email_address', '', 'maxlength="100" class="form-control"', false)}</div>
                        </div>
                        <div class="main_row">
                            <div class="main_title">{$smarty.const.ENTRY_TELEPHONE_NUMBER}</div>
                            <div class="main_value">{tep_draw_input_field('admin_phone_number', '', 'maxlength="100" class="form-control"', false)}</div>
                        </div>
                        {*<div class="main_row">
                            <div class="main_title">{$smarty.const.TEXT_ENTER_PASSWORD}</div>
                            <div class="main_value"><input type="password" name="password" class="form-control" autocomplete="off" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_PASSWORD}" /></div>
                        </div>*}
                        <div class="form-actions">
                            <button type="submit" class="submit btn btn-primary">
                                {$smarty.const.IMAGE_UPDATE} <i class="icon-angle-right"></i>
                            </button>
                        </div>
                            <div class="main_row">
                                <p>* {$smarty.const.TEXT_UPDATE_ACCOUNT_NOTE}</p>
                            </div>
                        {Html::endForm()}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>