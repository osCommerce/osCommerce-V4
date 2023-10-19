{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
{\frontend\design\Info::addBoxToCss('autocomplete')}

<div class="middle-form">
{Html::beginForm($action, 'post', ['id' => 'addressProcess'], false)}
    <div class="messages"></div>
    {assign var=re1 value='.{'}
    {assign var=re2 value='}'}
    {Html::hiddenInput('type', $type)}
    {Html::activeHiddenInput($model, 'address_book_id')}
    {Html::activeHiddenInput($model, 'type')}
	<div class="formWrapBox">
    {if $model->has('GENDER')}
        <div class="col-full col-gender">
            <div class="">{field_label const="ENTRY_GENDER" configuration=$model->get('GENDER')}</div>
            {Html::activeRadioList($model, 'gender', $model->getGendersList(), ['unselect' => null])}
        </div>
    {/if}
    {if $model->has('FIRSTNAME')}
        <div class="col-left">
            <label>
                <span>{field_label const="ENTRY_FIRST_NAME" configuration=$model->get('FIRSTNAME')}</span>
                {if $model->has('FIRSTNAME', false)}
                    {Html::activeTextInput($model, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}"])}
                {else}
                    {Html::activeTextInput($model, 'firstname')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('LASTNAME')}
        <div class="col-right">
            <label>
                <span>{field_label const="ENTRY_LAST_NAME" configuration=$model->get('LASTNAME')}</span>
                {if $model->has('LASTNAME', false)}
                    {Html::activeTextInput($model, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}"])}
                {else}
                    {Html::activeTextInput($model, 'lastname')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('POSTCODE')}
        <div class="col-left">
            <label>
                <span>{field_label const="ENTRY_POST_CODE" configuration=$model->get('POSTCODE')}</span>
                {if $model->has('POSTCODE', false)}
                    {Html::activeTextInput($model, 'postcode', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH)}"])}
                {else}
                    {Html::activeTextInput($model, 'postcode')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('STREET_ADDRESS')}
        <div class="col-right">
            <label>
                <span>{field_label const="ENTRY_STREET_ADDRESS" configuration=$model->get('STREET_ADDRESS')}</span>
                {if $model->has('STREET_ADDRESS', false)}
                    {Html::activeTextInput($model, 'street_address', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH)}"])}
                {else}
                    {Html::activeTextInput($model, 'street_address')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('SUBURB')}
        <div class="col-left">
            <label>
                <span>{field_label const="ENTRY_SUBURB" configuration=$model->get('SUBURB')}</span>
                {if $model->has('SUBURB', false)}
                    {Html::activeTextInput($model, 'suburb', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_SUBURB_ERROR}"])}
                {else}
                    {Html::activeTextInput($model, 'suburb')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('CITY')}
        <div class="col-right">
            <label>
                <span>{field_label const="ENTRY_CITY" configuration=$model->get('CITY')}</span>
                {if $model->has('CITY', false)}
                    {Html::activeTextInput($model, 'city', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH)}"])}
                {else}
                    {Html::activeTextInput($model, 'city')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('STATE')}
        <div class="col-left">
            <label>
                <span>{field_label const="ENTRY_STATE" configuration=$model->get('STATE')}</span>
                {if $model->has('STATE', false)}
                    {Html::activeTextInput($model, 'state', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH)}"])}
                {else}
                    {Html::activeTextInput($model, 'state')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('COUNTRY')}
        <div class="col-right">
            <label>
                <span>{field_label const="ENTRY_COUNTRY" configuration=$model->get('COUNTRY')}</span>
                {Html::activeDropDownList($model, 'country', $model->getAllowedCountries(), ['data-required' => "{$smarty.const.ENTRY_COUNTRY_ERROR}", 'data-iso' => $model->getAllowedCountriesISO()])}
            </label>
        </div>
    {/if}
    {if $model->has('COMPANY')}
        <div class="col-left">
            <label>
                <span>{field_label const="ENTRY_COMPANY" configuration=$model->get('COMPANY')}</span>
                {if $model->has('COMPANY', false)}
                    {Html::activeTextInput($model, 'company', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_COMPANY_ERROR}"])}
                {else}
                    {Html::activeTextInput($model, 'company')}
                {/if}
            </label>
        </div>
    {/if}
    {if $model->has('COMPANY_VAT')}
        <div class="col-right">
            <label>
                <span>{field_label const="ENTRY_BUSINESS" configuration=$model->get('COMPANY_VAT')}</span>
                {if $model->has('COMPANY_VAT', false)}
                    {Html::activeTextInput($model, 'company_vat', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_VAT_ID_ERROR}"])}
                {else}
                    {Html::activeTextInput($model, 'company_vat')}
                {/if}
                <span id="company_vat_status"></span>
            </label>
        </div>
    {/if}
    {if $model->has('CUSTOMS_NUMBER')}
        <div class="col-right">
            <label>
                <span>{field_label const="TEXT_CUSTOMS_NUMBER" configuration=$model->get('CUSTOMS_NUMBER')}</span>
                {if $model->has('CUSTOMS_NUMBER', false)}
                    {Html::activeTextInput($model, 'customs_number', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.TEXT_CUSTOMS_NUMBER_ERROR}"])}
                {else}
                    {Html::activeTextInput($model, 'customs_number')}
                {/if}
                <span id="customs_number_status"></span>
            </label>
        </div>
    {/if}
    {if $model->has('TELEPHONE')}
        <div class="col-left">
            <label>
                <span>{field_label const="ENTRY_TELEPHONE_ADRESS_BOOK" configuration=$model->get('TELEPHONE')}</span>
                {if $model->has('TELEPHONE', false)}
                    {Html::activeTextInput($model, 'telephone', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_TELEPHONE_ADRESS_BOOK_ERROR}"])}
                {else}
                    {Html::activeTextInput($model, 'telephone')}
                {/if}
                <i class="telephone_status"></i>
            </label>
        </div>
    {/if}
    {if $model->has('EMAIL_ADDRESS')}
        <div class="col-right">
            <label>
                <span>{field_label const="ENTRY_EMAIL_ADDRESS_ADRESS_BOOK" configuration=$model->get('TELEPHONE')}</span>
                {if $model->has('EMAIL_ADDRESS', false)}
                    {Html::activeTextInput($model, 'email_address', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_EMAIL_ADDRESS_ADRESS_BOOK_ERROR}"])}
                {else}
                    {Html::activeTextInput($model, 'email_address')}
                {/if}
            </label>
        </div>
    {/if}
    {if $set_primary}
    <div class="col-full col-gender">
        {Html::activeCheckbox($model, 'as_preferred', ['label' => SET_AS_PRIMARY])}
    </div>
    {/if}
    <div class="center-buttons">{$links.update}</div>
    {Html::endForm()}
	</div>
</div>
    {if !empty($postcoder)}
    {$postcoder->drawAccountPostcodeHelper($model)}
    {/if}
<script>
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/jquery-ui.min.js')}',
        '{Info::themeFile('/js/address.js')}'
    ], function(){
        var form = $('#box-{$id} form');
        $('input', form).validate();
        var key = true;
        form.on('submit', function(){
            if ($('.required-error', form).length === 0 && key){
                //key = false;
                $.post(form.attr('action'), form.serialize(), function(data){
                    var messages = '';
                    key = true;
                    $.each(data.messages, function(key, val){
                        messages += '<div class="message '+val['type']+'">'+val.text+'</div>';
                        if (val['type'] === 'success') {
                            key = false;
                            setTimeout(function(){
                                if (form.closest('.popup-box').length > 0) {
                                    $('.pop-up-close').trigger('click')
                                } else {
                                    window.location=document.referrer;
                                }
                            }, 1000)
                        }
                    });
                    $('.messages', form).html(messages)
                }, 'json')
            }
            return false;
        });
        
        $('#{Html::getInputId($model, 'state')}').setStateCountryDependency({
            'country': '#{Html::getInputId($model, 'country')}',
            'url': "{Yii::$app->urlManager->createUrl('account/address-state')}",
        });
        
        $('#{Html::getInputId($model, 'city')}').getCityList({
            'country': '#{Html::getInputId($model, 'country')}',
            'url': "{Yii::$app->urlManager->createUrl('account/address-city')}",
        });
        
    });
</script>
