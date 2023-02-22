{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{use class="yii\captcha\Captcha"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
<div class="login-box">
    {if isset($settings['tabsManually']) && $settings['tabsManually']}
        <div class="login-box-heading">{$smarty.const.RETURNING_CUSTOMER}</div>
    {/if}
    <div class="middle-form">

        {if $b2b = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')}
            {Html::beginForm($action, 'post', ['onsubmit' => "return checkTerms(this);", 'class' => 'b2bLogin'])}
        {else}
            {Html::beginForm($action, 'post', [])}
        {/if}
        {Html::hiddenInput('scenario', $loginModel->formName())}
        <div class="col-left">
            <label for="email_address">{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label>
            {Html::activeInput('text', $loginModel, 'email_address', ['autocomplete' => "off"])}
        </div>
        <div class="col-right">
            <label for="password1">{field_label const="PASSWORD" required_text=""}</label>
            {Html::activePasswordInput($loginModel, 'password', ['autocomplete' => "off", 'class' => 'show-password'])}
        </div>

        {if in_array($loginModel->captha_enabled, ['captha', 'recaptha'])}
            <div class="captcha-holder">
                {$loginModel->captcha_widget}
            </div>
        {/if}

        {if $b2b = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')}
            {if $b2b::checkNeedLogin()}
        <div class="login_btns after">
            <div class="terms-login">
                {Html::activeCheckbox($loginModel, 'terms', ['class' => 'terms-conditions', 'value' => '1', 'label' => '<strong>'|cat:$SMARTY.CONST.ACCEPT|cat:'</strong>'|cat:$smarty.const.TEXT_TERMS_CONDITIONS, 'checked' => ''])}
            </div>
        </div>
            {/if}
        {/if}
        <div class="password-forgotten-link">
            <a href="{tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL')}">{$smarty.const.TEXT_PASSWORD_FORGOTTEN_S}</a>
        </div>
        <div class="center-buttons">
            <button class="btn-2" type="submit">{$smarty.const.SIGN_IN}</button>
        </div>
        {*<div class="info">{$smarty.const.CART_MERGED} (<a href="#cart-merged" class="pop-up-link">{$smarty.const.MORE_INFO}</a>)</div>
        <div id="cart-merged" style="display: none;">
          <div class="pop-up-info">
            <div class="heading-4">{$smarty.const.SUB_HEADING_TITLE_1}</div>
            <p>{$smarty.const.SUB_HEADING_TEXT_1}</p>
            <div class="heading-4">{$smarty.const.SUB_HEADING_TITLE_2}</div>
            <p>{$smarty.const.SUB_HEADING_TEXT_2}</p>
            <div class="heading-4">{$smarty.const.SUB_HEADING_TITLE_3}</div>
            <p>{$smarty.const.SUB_HEADING_TEXT_3}</p>
          </div>
          <div class="center-buttons">
            <span class="btn btn-cancel">{$smarty.const.CONTINUE}</span>
          </div>
        </div>*}
        {Html::endForm()}
    </div>
</div>

<script>
    function checkTerms(form) {
        {if $b2b = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')}
            {if $b2b::checkNeedLogin()}
                if (form.querySelector('.terms-conditions').checked){
                    return true;
                }
                alertMessage('{$smarty.const.TEXT_PLEASE_TERMS}');
                return false;
            {/if}
        {/if}
        return true;
    }


    tl('{Info::themeFile('/js/main.js')}', function(){

        {if isset($messages_login) && $messages_login}
        alertMessage('{$messages_login|escape:'javascript'}');
        {/if}

        $('.terms-popup').popUp({
            box_class: 'terms-info-popup'
        });

        $('.show-password').showPassword();
    })
</script>
