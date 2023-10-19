{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}

{Info::addBoxToCss('info')}
{Info::addBoxToCss('form')}
{Info::addBoxToCss('pass-strength')}
{Info::addBoxToCss('info-popup')}
{Info::addBoxToCss('switch')}
{Info::addBoxToCss('datepicker')}

<div class="login-box">
    {if isset($settings['tabsManually']) && $settings['tabsManually']}
        <div class="login-box-heading">{$smarty.const.REGISTER}</div>
    {/if}
            <div class="middle-form">
              {if isset($messages_registration)}
                {$messages_registration}
              {/if}
                {assign var=re1 value='.{'}
                {assign var=re2 value='}'}
                
{if $smarty.const.PASSWORD_STRONG_REQUIRED eq 'ULNS'}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ULNS_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='(?=.*\d)(?=.*\W+)(?=.*[a-z])(?=.*[A-Z]).{'|cat:$smarty.const.ENTRY_PASSWORD_MIN_LENGTH|cat:',}'}
{elseif $smarty.const.PASSWORD_STRONG_REQUIRED eq 'ULN'}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ULN_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{'|cat:$smarty.const.ENTRY_PASSWORD_MIN_LENGTH|cat:',}'}
{else}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='.{'|cat:$smarty.const.ENTRY_PASSWORD_MIN_LENGTH|cat:'}'}
{/if}

                {Html::beginForm($action, 'post', ['name' => 'register'])}
                {Html::hiddenInput('scenario', $registerModel->formName())}                
                {if isset($wr_registry_id) && $wr_registry_id}
                    <input type="hidden" name="wr_registry_id" value="{$wr_registry_id}">
                {/if}
                
                {if in_array(ACCOUNT_GENDER, ['required_register', 'visible_register'])}
                    <div class="col-full col-gender">
                        <span>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</span>
                        {assign var="options" value=[]}
                        {if ACCOUNT_GENDER == 'required_register' }{$options['required'] = 'required'}{/if}
                        {Html::activeRadioList($registerModel, 'gender', $registerModel->getGenderList(), $options)}                        
                    </div>
                {/if}
                {if in_array(ACCOUNT_FIRSTNAME, ['required_register', 'visible_register'])}
                    <div class="col-left">
                        <label for="{$registerModel->formName()}-firstname">{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>                        
                        {if ACCOUNT_FIRSTNAME == 'required_register'}
                            {Html::activeTextInput($registerModel, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'autocomplete' => 'given-name'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'firstname', ['class' => "skip-validation", 'autocomplete' => 'given-name'])}
                        {/if}
                    </div>
                {/if}
                {if in_array(ACCOUNT_LASTNAME, ['required_register', 'visible_register'])}
                    <div class="col-right">
                        <label for="{$registerModel->formName()}-lastname">{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
                        {if ACCOUNT_LASTNAME == 'required_register'}
                            {Html::activeTextInput($registerModel, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'autocomplete' => 'family-name'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'lastname', ['autocomplete' => 'family-name'])}
                        {/if}
                    </div>
                {/if}
				
                <div class="password-row">
				<div class="generate_row">
					<a href="#" class="generate_password">{$smarty.const.TEXT_GENERATE_PASSWORD}</a>
				</div>
                    <div class="col-left">
                        <label for="{$registerModel->formName()}-password" class="password-info">
                            <div class="info-popup top-left"><div>{sprintf($smarty.const.TEXT_HELP_PASSWORD, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH, $smarty.const.STORE_NAME)}</div></div>
                            {field_label const="PASSWORD" required_text="*"}
                        </label>
                        {Html::activePasswordInput($registerModel, 'password', ['class' => "password show-password", 'autocomplete' => "new-password", 'data-pattern' => "{$passDataPattern}", 'data-required' => "{$titleDataPattern}"])}
                    </div>
                    <div class="col-right">
                        <label for="confirmation">{field_label const="PASSWORD_CONFIRMATION" required_text="*"}</label>
                        {Html::activePasswordInput($registerModel, 'confirmation', ['class' => "confirmation show-password", 'autocomplete' => "new-password", 'data-required' => "{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}", 'data-confirmation' => "#registration-password"])}
                    </div>
                </div>
                <div class="col-left">
                    <label for="{$registerModel->formName()}-email_address">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
                    {Html::activeInput('email', $registerModel, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email", 'autocomplete' => 'email'])}
                </div>
                {if in_array(ACCOUNT_TELEPHONE, ['required_register', 'visible_register'])}
                    <div class="col-right">
                        <label for="{$registerModel->formName()}-telephone">{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                        {if ACCOUNT_TELEPHONE == 'required_register'}
                            {Html::activeTextInput($registerModel, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}", 'autocomplete' => 'tel'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'telephone', ['autocomplete' => 'tel'])}
                        {/if}
                    </div>
                {/if}
				{if in_array(ACCOUNT_COMPANY, ['required_register', 'visible_register'])}
                    <div class="col-left">
                        <label for="{$registerModel->formName()}-company">{field_label const="ENTRY_COMPANY" configuration="ACCOUNT_COMPANY"}</label>                        
                        {if ACCOUNT_COMPANY == 'required_register'}
                            {Html::activeTextInput($registerModel, 'company', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_COMPANY_ERROR}", 'autocomplete' => 'organization'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'company', ['autocomplete' => 'organization'])}
                        {/if}
                    </div>
                {/if}
                {if in_array(ACCOUNT_COMPANY_VAT, ['required_register', 'visible_register'])}
                    <div class="col-right">
                        <label for="{$registerModel->formName()}-company_vat">{field_label const="ENTRY_BUSINESS" configuration="ACCOUNT_COMPANY_VAT"}</label>
                        {if ACCOUNT_COMPANY_VAT == 'required_register'}
                            {Html::activeTextInput($registerModel, 'company_vat', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_VAT_ID_ERROR}", 'autocomplete' => 'company_vat'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'company_vat', ['autocomplete' => 'company_vat'])}
                        {/if}
                    </div>
                {/if}
                {if in_array(ACCOUNT_CUSTOMS_NUMBER, ['required_register', 'visible_register'])}
                    <div class="col-right">
                        <label for="{$registerModel->formName()}-customs_number">{field_label const="TEXT_CUSTOMS_NUMBER" configuration="ACCOUNT_CUSTOMS_NUMBER"}</label>
                        {if ACCOUNT_CUSTOMS_NUMBER == 'required_register'}
                            {Html::activeTextInput($registerModel, 'customs_number', ['data-required' => "{$smarty.const.TEXT_CUSTOMS_NUMBER_ERROR}", 'autocomplete' => 'customs_number'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'customs_number', ['autocomplete' => 'customs_number'])}
                        {/if}
                    </div>
                {/if}
                {if in_array(ACCOUNT_LANDLINE, ['required_register', 'visible_register'])}
                    <div class="col-left">
                        <label for="{$registerModel->formName()}-landline">{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
                        {if ACCOUNT_LANDLINE == 'required_register'}
                            {Html::activeTextInput($registerModel, 'landline', ['data-required' => "{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}", 'autocomplete' => 'landline'])}
                        {else}
                            {Html::activeTextInput($registerModel, 'landline', ['autocomplete' => 'landline'])}
                        {/if}
                    </div>
                {/if}
                {if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && ACCOUNT_GDPR == 'true'}
                    <div class="col-full-padding">
                        <div class="col-left col-full-margin" style="padding-top: 5px">
                            <label for="gdpr" style="display: inline;" class="slim">
                                {Html::activeCheckbox($registerModel, 'gdpr', ['class' => "candlestick gdpr", 'label' => {$smarty.const.TEXT_AGE_OVER}, 'value' => $registerModel->gdpr])}
                                <span class="checkbox-span"></span>
                            </label>
                        </div>
                        <div class="col-right dob-hide" style="display: none;">
                            <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
                            <div class="" style="position: relative">
                                {assign var="options" value = ['class' => "datepicker dobTmp"]}
                                {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                                {Html::activeTextInput($registerModel, 'dobTmp', $options, ['autocomplete' => 'bday'])}
                                {Html::activeHiddenInput($registerModel, 'dob', ['class' => 'dob-res'])}
                            </div>
                        </div>
                    </div>
                {elseif in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])}
                    <div class="col-right">
                        <label for="dob">{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"} </label>
                        <div class="" style="position: relative">
                            {assign var="options" value = ['class' => "datepicker dobTmp"]}
                            {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                            {Html::activeTextInput($registerModel, 'dobTmp', $options, ['autocomplete' => 'bday'])}
                            {Html::activeHiddenInput($registerModel, 'dob', ['class' => 'dob-res'])}
                        </div>
                    </div>
                {/if}
                {if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true' }
                    <div class="col-left">
                        <label class="slim">
                            {Html::activeCheckbox($registerModel, 'newsletter', ['class' => 'candlestick newsletter', 'value' => '', 'label' => {$smarty.const.RECEIVE_REGULAR_OFFERS}, 'value' => $registerModel->newsletter ])}
                            <span class="checkbox-span"></span>
                        </label>
                    </div>

                    <div class="col-right regular_offers_box" style="display: none;">
                        <label for="{$registerModel->formName()}-regular_offers">{$smarty.const.RECEIVE_REGULAR_OFFERS_PERIOD}</label>
                        {Html::activeDropDownList($registerModel, 'regular_offers', $registerModel->getRegularOfferList())}
                    </div>
                {/if}
                {if $showAddress}
                    {if in_array(ACCOUNT_POSTCODE, ['required_register', 'visible_register'])}
                        <div class="col-left">
                            <label for="{$registerModel->formName()}-postcode">{field_label const="ENTRY_POST_CODE" configuration="ACCOUNT_POSTCODE"}</label>
                            {if ACCOUNT_POSTCODE == 'required_register'}
                                 {Html::activeTextInput($registerModel, 'postcode', ['data-required' => "{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, $smarty.const.ENTRY_POSTCODE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}", 'autocomplete' => 'postal-code'])}
                            {else}
                                {Html::activeTextInput($registerModel, 'postcode', ['autocomplete' => 'postal-code'])}
                            {/if}
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_STREET_ADDRESS, ['required_register', 'visible_register'])}
                        <div class="col-right">
                            <label for="{$registerModel->formName()}-street_address">{field_label const="ENTRY_STREET_ADDRESS" configuration="ACCOUNT_STREET_ADDRESS"}</label>
                            {if ACCOUNT_STREET_ADDRESS == 'required_register'}
                                {Html::activeTextInput($registerModel, 'street_address', ['data-required' => "{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, $smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}", 'autocomplete' => 'street-address'])}
                            {else}
                                {Html::activeTextInput($registerModel, 'street_address', ['autocomplete' => 'street-address'])}
                            {/if}
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_SUBURB, ['required_register', 'visible_register'])}
                        <div class="col-left">
                            <label for="{$registerModel->formName()}-suburb">{field_label const="ENTRY_SUBURB" configuration="ACCOUNT_SUBURB"}</label>
                            {if ACCOUNT_SUBURB == 'required_register'}
                                {Html::activeTextInput($registerModel, 'suburb', ['data-required' => "{$smarty.const.ENTRY_SUBURB_ERROR}", 'data-pattern' => "{$re1}1{$re2}", 'autocomplete' => 'address-line1'])}
                            {else}
                                {Html::activeTextInput($registerModel, 'suburb', ['autocomplete' => 'address-line1'])}
                            {/if}
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_CITY, ['required_register', 'visible_register'])}
                        <div class="col-right">
                            <label for="{$registerModel->formName()}-city">{field_label const="ENTRY_CITY" configuration="ACCOUNT_CITY"}</label>
                            {if ACCOUNT_CITY == 'required_register'}
                                {Html::activeTextInput($registerModel, 'city', ['data-required' => "{sprintf($smarty.const.ENTRY_CITY_ERROR, $smarty.const.ENTRY_CITY_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}", 'autocomplete' => 'address-level2'])}
                            {else}
                                {Html::activeTextInput($registerModel, 'city', ['autocomplete' => 'address-level2'])}
                            {/if}
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_STATE, ['required_register', 'visible_register'])}
                        <div class="col-left">
                            <label for="{$registerModel->formName()}-state">{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
                            {if ACCOUNT_STATE == 'required_register'}
                                {Html::activeTextInput($registerModel, 'state', ['class' => 'state', 'data-required' => "{sprintf($smarty.const.ENTRY_STATE_ERROR, $smarty.const.ENTRY_STATE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}", 'autocomplete' => 'address-level1'])}
                            {else}
                                {Html::activeTextInput($registerModel, 'state', ['autocomplete' => 'address-level1'])}
                            {/if}
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_COUNTRY, ['required_register', 'visible_register'])}
                        <div class="col-right">
                            <label for="{$registerModel->formName()}-country">{field_label const="ENTRY_COUNTRY" configuration="ACCOUNT_COUNTRY"}</label>
                            {Html::activedropDownList($registerModel, 'country', \common\helpers\Country::new_get_countries('', false), ['class' => 'country', 'required' => (ACCOUNT_COUNTRY == 'required_register'), 'value' => $registerModel->getDefaultCountryId(), 'autocomplete' => 'country'])}
                        </div>
                    {/if}
                {/if}
                {if ENABLE_CUSTOMER_GROUP_CHOOSE == 'True'}
                    <div class="col-right">
                        <label for="{$registerModel->formName()}-group">{$smarty.const.ENTRY_GROUP}</label>
                        {Html::activedropDownList($registerModel, 'group', \common\helpers\Group::get_customer_groups_list())}
                    </div>
                {/if}
                {foreach \common\helpers\Hooks::getList('box/login/register', 'after-main-fields') as $filename}
                    {include file=$filename}
                {/foreach}
                {if in_array($registerModel->captha_enabled, ['captha', 'recaptha'])}
                    <div class="captcha-holder">
                        {$registerModel->captcha_widget}
                    </div>
                {/if}
                {if $verifyEmail}    
                <div id="email_validation_box" class="center-buttons">
                    <button class="btn-2" type="button" onclick="sendValidationRequest();">{$smarty.const.TEXT_EMAIL_VERIFICATION}</button>
                </div>
                {/if}
                <div id="register_buttons_box"{if $verifyEmail} style="display: none;"{/if}>
                    {if $verifyEmail}
                        <div class="col-full">
                            <label>{$smarty.const.TEXT_VERIFICATION_CODE}:</label>
                            <input type="text" name="email_verification_code" value="">
                        </div>
                    {/if}
                    <div class="col-full privacy-row">
                        <div class="terms-login">
                            {Html::activeCheckbox($registerModel, 'terms', ['class' => 'terms-conditions', 'value' => '1', 'label' => '', 'checked' => false])}{$smarty.const.TEXT_TERMS_CONDITIONS}
                        </div>
                    </div>
                    <div class="center-buttons">
                        <button class="btn-2 disabled-area" type="submit">{$smarty.const.CREATE}</button>
                    </div>
                </div>
                {Html::endForm()}
            </div>
</div>

<script type="text/javascript">
{if $verifyEmail} 
function sendValidationRequest()
{
    var email = $('#registration-email_address').val();
    if (email === '') {
        alertMessage('{$smarty.const.EMPTY_EMAIL_ERROR|escape:javascript}');
        return false;
    }
    $.get('{$app->urlManager->createUrl('account/send-validation-request')}', { 'email': email }, function(data){
        $('#register_buttons_box').show();
        $('#email_validation_box').hide();
    }, 'json');
    return false;
}
{/if} 
    tl(function(){

        {if isset($messages_registration) && $messages_registration}
        alertMessage('{$messages_registration}');
        {/if}
    })

    var ageStatement = 'default';
    var offersStatement = 'default';

    tl([        
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/password-strength.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
        '{Info::themeFile('/js/hammer.js')}',
        '{Info::themeFile('/js/candlestick.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-datepicker.js')}',        
    ], function () {
        var box = $('#box-{$id}');
        var dob = $('.dobTmp', box);

        $('head').prepend('<link rel="stylesheet" href="{Info::themeFile('/css/bootstrap-datepicker.css')}">');

        $.fn.datepicker.dates.current={
            days:["{$smarty.const.TEXT_SUNDAY}","{$smarty.const.TEXT_MONDAY}","{$smarty.const.TEXT_TUESDAY}","{$smarty.const.TEXT_WEDNESDAY}","{$smarty.const.TEXT_THURSDAY}","{$smarty.const.TEXT_FRIDAY}","{$smarty.const.TEXT_SATURDAY}"],
            daysShort:["{$smarty.const.DATEPICKER_DAY_SUN}","{$smarty.const.DATEPICKER_DAY_MON}","{$smarty.const.DATEPICKER_DAY_TUE}","{$smarty.const.DATEPICKER_DAY_WED}","{$smarty.const.DATEPICKER_DAY_THU}","{$smarty.const.DATEPICKER_DAY_FRI}","{$smarty.const.DATEPICKER_DAY_SAT}"],
            daysMin:["{$smarty.const.DATEPICKER_DAY_SU}","{$smarty.const.DATEPICKER_DAY_MO}","{$smarty.const.DATEPICKER_DAY_TU}","{$smarty.const.DATEPICKER_DAY_WE}","{$smarty.const.DATEPICKER_DAY_TH}","{$smarty.const.DATEPICKER_DAY_FR}","{$smarty.const.DATEPICKER_DAY_SA}"],
            months:["{$smarty.const.DATEPICKER_MONTH_JANUARY}","{$smarty.const.DATEPICKER_MONTH_FEBRUARY}","{$smarty.const.DATEPICKER_MONTH_MARCH}","{$smarty.const.DATEPICKER_MONTH_APRIL}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUNE}","{$smarty.const.DATEPICKER_MONTH_JULY}","{$smarty.const.DATEPICKER_MONTH_AUGUST}","{$smarty.const.DATEPICKER_MONTH_SEPTEMBER}","{$smarty.const.DATEPICKER_MONTH_OCTOBER}","{$smarty.const.DATEPICKER_MONTH_NOVEMBER}","{$smarty.const.DATEPICKER_MONTH_DECEMBER}"],
            monthsShort:["{$smarty.const.DATEPICKER_MONTH_JAN}","{$smarty.const.DATEPICKER_MONTH_FEB}","{$smarty.const.DATEPICKER_MONTH_MAR}","{$smarty.const.DATEPICKER_MONTH_APR}","{$smarty.const.DATEPICKER_MONTH_MAY}","{$smarty.const.DATEPICKER_MONTH_JUN}","{$smarty.const.DATEPICKER_MONTH_JUL}","{$smarty.const.DATEPICKER_MONTH_AUG}","{$smarty.const.DATEPICKER_MONTH_SEP}","{$smarty.const.DATEPICKER_MONTH_OCT}","{$smarty.const.DATEPICKER_MONTH_NOV}","{$smarty.const.DATEPICKER_MONTH_DEC}"],
            today:"{$smarty.const.TEXT_TODAY|strip}",
            clear:"{$smarty.const.TEXT_CLEAR|strip}",
            weekStart:1
        };

        dob.datepicker({
            startView: 3,
            format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
            language: 'current',
            autoclose: true
        }).on('changeDate', function(e){
            var date = e.date;
            $('.dob-res', box).val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
        }).removeClass('required-error').next('.required-message-wrap').remove();


        $('.password', box).passStrength({
            shortPassText: "{$smarty.const.TEXT_TOO_SHORT|strip}",
            badPassText: "{$smarty.const.TEXT_WEAK|strip}",
            goodPassText: "{$smarty.const.TEXT_GOOD|strip}",
            strongPassText: "{$smarty.const.TEXT_STRONG|strip}",
            samePasswordText: "{$smarty.const.TEXT_USERNAME_PASSWORD_IDENTICAL|strip}",
            userid: "#firstname"
        });

        $('.confirmation, .password', box).on('keyup', function () {
            var confirmation = $('.confirmation', box);
            if (confirmation.val() !== $('.password', box).val() && confirmation.val()) {
                confirmation.prev(".pass-strength").remove();
                confirmation.before('<span class="pass-strength pass-no-match"><span>{$smarty.const.TEXT_NO_MATCH|strip}</span></span>');
            } else if (confirmation.val() === '') {
                confirmation.prev(".pass-strength").remove();
            } else {
                confirmation.prev(".pass-strength").remove();
                confirmation.before('<span class="pass-strength pass-match"><span>{$smarty.const.TEXT_MATCH|strip}</span></span>');
            }
        });

        {if isset($create_tab_active) && $create_tab_active}
        box.parents('.block').each(function(){
            $('a[data-href="#' + $(this).attr('id') + '"]').trigger('click')
        });
        {/if}


        $('.pop-up-link').popUp();

        $('.middle-form input', box).validate();

        var disableButton = function(e){
            e.preventDefault();
            return false;
        };

        $('.disabled-area', box).on('click', disableButton);

        $(".check-on-off", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function () {
                $(this).closest('form').trigger('cart-change')
            }
        });
        
        $(".terms-conditions", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                var form = $(this).closest('form');
                form.trigger('cart-change');
                if(e){
                    $('button[type="submit"]', form).removeClass('disabled-area').off('click', disableButton);
                }else{
                    $('button[type="submit"]', form).addClass('disabled-area').on('click', disableButton);
                }
            }
        });
        
        {if $registerModel->isShowAddress()}
            tl(['{Info::themeFile('/js/jquery-ui.min.js')}', '{Info::themeFile('/js/address.js')}'], function(){
                $('.state').setStateCountryDependency({
                    'country': 'select.country',
                    'url': "{Yii::$app->urlManager->createUrl('account/address-state')}",
                });
            });
        {/if}        
        
        $('.candlestick', box).candlestick({
            afterAction: function(obj, wrap, val) {
                if ($(obj).hasClass('newsletter')) {
                    offersStatement = val;
                    if (val === 'on') {
                        $('.regular_offers_box', box).show();
                    } else {
                        $('.regular_offers_box', box).hide();
                    }
                }
                if ($(obj).hasClass('gdpr')) {
                    ageStatement = val;
                    if (val === 'on') {
                        dob.attr('disabled', 'disabled').addClass('skip-validation');
                        $('.dob-hide', box).hide();
                    } else if (val === 'default') {
                        dob.removeAttr('disabled').removeClass('skip-validation');
                        $('.dob-hide', box).hide();
                    } else {
                        dob.removeAttr('disabled').removeClass('skip-validation');
                        $('.dob-hide', box).show();
                    }
                }
            }
        });

        var count = 0;
        $('form', box).on('submit', function(e){
            if (!document.register.querySelector('.terms-conditions').checked){
                alertMessage('{$smarty.const.TEXT_PLEASE_TERMS}');
                return false;
            }            
{if in_array(ACCOUNT_DOB, ['required_register', 'visible_register']) && ACCOUNT_GDPR == 'true'}
            if (ageStatement === 'default') {
                alertMessage('{$smarty.const.TEXT_PLEASE_AGE}');
                return false;
            }
{/if}

{if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true'}
            if (offersStatement === 'default') {
                alertMessage('{$smarty.const.TEXT_PLEASE_OFFERS}');
                return false;
            }
{/if}
            
            if (count > 0){
                setTimeout(function(){
                    count = 0
                }, 1000);
                e.preventDefault();
                return false;
            }
            count++;
        });

        $('.show-password').showPassword();
		$('.generate_password').on('click', function(){
			var _form = $(this).closest('form');
            $.get('{$app->urlManager->createUrl('account/generate-password')}', function(data){
                $('.show-password', _form).val(data);
                $('.show-password', _form).trigger('keyup');
                $('.password-row .col-right', _form).hide();
				$('.password-row .col-left', _form).css('width','100%');
				if($('.password-row .col-left .show-password', _form).attr('type') == 'password'){
                  $('.password-row .col-left .eye-password', _form).click();
                }
            }, 'json')
			return false;
		})
		$('.show-password').on('keyup', function(){
			var _form = $(this).closest('form');
			if(!$('.password-row .col-right', _form).is(':visible')){
				$('.password-row .col-right', _form).show();
				$('.password-row .col-left', _form).css('width','48%');
			}
		})
    })

</script>