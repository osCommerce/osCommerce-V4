{use class="\common\helpers\Html"}
{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('autocomplete')}
{Info::addBoxToCss('select-suggest')}

    {assign var=re1 value='.{'}
    {assign var=re2 value='}'}
    {Html::activeHiddenInput($model, 'address_book_id')}
    {Html::activeHiddenInput($model, 'drop_ship', ['value' => $drop_ship])}
    {Html::activeHiddenInput($model, 'type')}
    {if $model->has('GENDER')}
        <div class="col-full genders-title">
            <div class="">{field_label const="ENTRY_GENDER" configuration=$model->get('GENDER')}</div>
            {Html::activeRadioList($model, 'gender', $model->getGendersList(), ['unselect' => null])}
        </div>
    {/if}
    <div class="columns form-inputs">
        {if $model->has('FIRSTNAME')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_FIRST_NAME" configuration=$model->get('FIRSTNAME')}</span>
                    {if $model->has('FIRSTNAME', false)}
                        {Html::activeTextInput($model, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'autocomplete' => 'given-name'])}
                    {else}
                        {Html::activeTextInput($model, 'firstname', ['autocomplete' => 'given-name'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('LASTNAME')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_LAST_NAME" configuration=$model->get('LASTNAME')}</span>
                    {if $model->has('LASTNAME', false)}
                        {Html::activeTextInput($model, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'autocomplete' => 'family-name'])}
                    {else}
                        {Html::activeTextInput($model, 'lastname', ['autocomplete' => 'family-name'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('STREET_ADDRESS')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_STREET_ADDRESS" configuration=$model->get('STREET_ADDRESS')}</span>
                    {if $model->has('STREET_ADDRESS', false)}
                        {Html::activeTextInput($model, 'street_address', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH)}", 'autocomplete' => 'street-address'])}
                    {else}
                        {Html::activeTextInput($model, 'street_address', ['autocomplete' => 'street-address'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('SUBURB')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_SUBURB" configuration=$model->get('SUBURB')}</span>
                    {if $model->has('SUBURB', false)}
                        {Html::activeTextInput($model, 'suburb', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_SUBURB_ERROR}", 'autocomplete' => 'address-line1'])}
                    {else}
                        {Html::activeTextInput($model, 'suburb', ['autocomplete' => 'address-line1'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('POSTCODE')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_POST_CODE" configuration=$model->get('POSTCODE')}</span>
                    {if $model->has('POSTCODE', false)}
                        {Html::activeTextInput($model, 'postcode', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH)}", 'autocomplete' => 'postal-code'])}
                    {else}
                        {Html::activeTextInput($model, 'postcode', ['autocomplete' => 'postal-code'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('CITY')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_CITY" configuration=$model->get('CITY')}</span>
                    {if $model->has('CITY', false)}
                        {Html::activeTextInput($model, 'city', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH)}", 'autocomplete' => 'address-level2'])}
                    {else}
                        {Html::activeTextInput($model, 'city', ['autocomplete' => 'address-level2'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('STATE')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_STATE" configuration=$model->get('STATE')}</span>
                    {if $model->has('STATE', false)}
                        {Html::activeTextInput($model, 'state', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH)}", 'autocomplete' => 'address-level1'])}
                    {else}
                        {Html::activeTextInput($model, 'state', ['autocomplete' => 'address-level1'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('COUNTRY')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_COUNTRY" configuration=$model->get('COUNTRY')}</span>
                    {Html::activeDropDownList($model, 'country', $model->getAllowedCountries(), ['data-required' => "{$smarty.const.ENTRY_COUNTRY_ERROR}", 'class' => ' select2 select2-offscreen', 'data-iso' => $model->getAllowedCountriesISO() ])}
                </label>
            </div>
        {/if}
        {if $model->has('COMPANY')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_COMPANY" configuration=$model->get('COMPANY')}</span>
                    {if $model->has('COMPANY', false)}
                        {Html::activeTextInput($model, 'company', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_COMPANY_ERROR}", 'autocomplete' => 'organization'])}
                    {else}
                        {Html::activeTextInput($model, 'company', ['autocomplete' => 'organization'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('COMPANY_VAT')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_BUSINESS" configuration=$model->get('COMPANY_VAT')}</span>
                    {if $model->has('COMPANY_VAT', false)}
                        {Html::activeTextInput($model, 'company_vat', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_VAT_ID_ERROR}", 'autocomplete' => 'company_vat'])}
                    {else}
                        {Html::activeTextInput($model, 'company_vat', ['autocomplete' => 'company_vat'])}
                    {/if}
                    <i class="company_vat_status"></i>
                </label>
            </div>
        {/if}
        {if $model->has('CUSTOMS_NUMBER')}
            <div class="col-2">
                <label>
                    <span>{field_label const="TEXT_CUSTOMS_NUMBER" configuration=$model->get('CUSTOMS_NUMBER')}</span>
                    {if $model->has('CUSTOMS_NUMBER', false)}
                        {Html::activeTextInput($model, 'customs_number', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.TEXT_CUSTOMS_NUMBER_ERROR}", 'autocomplete' => 'customs_number'])}
                    {else}
                        {Html::activeTextInput($model, 'customs_number', ['autocomplete' => 'customs_number'])}
                    {/if}
                    <i class="customs_number_status"></i>
                </label>
            </div>
        {/if}
        {if $model->has('TELEPHONE')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_TELEPHONE_ADRESS_BOOK" configuration=$model->get('TELEPHONE')}</span>
                    {if $model->has('TELEPHONE', false)}
                        {Html::activeTextInput($model, 'telephone', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_TELEPHONE_ADRESS_BOOK_ERROR}", 'autocomplete' => 'tel'])}
                    {else}
                        {Html::activeTextInput($model, 'telephone', ['autocomplete' => 'tel'])}
                    {/if}
                    <i class="telephone_status"></i>
                </label>
            </div>
        {/if}
        {if $model->has('EMAIL_ADDRESS')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_EMAIL_ADDRESS_ADRESS_BOOK" configuration=$model->get('EMAIL_ADDRESS')}</span>
                    {if $model->has('EMAIL_ADDRESS', false)}
                        {Html::activeTextInput($model, 'email_address', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_EMAIL_ADDRESS_ADRESS_BOOK_ERROR}", 'autocomplete' => 'email'])}
                    {else}
                        {Html::activeTextInput($model, 'email_address', ['autocomplete' => 'email'])}
                    {/if}
                </label>
            </div>
        {/if}
        <div class="col-2" style="display: none">
            <label>
                {if ($model::SHIPPING_ADDRESS == $model->addressType && $model->address_book_id != $manager->getSendto()) 
                ||
                ($model::BILLING_ADDRESS == $model->addressType && $model->address_book_id != $manager->getBillto()) }
                {Html::activeCheckbox($model, 'as_preferred')}
                {/if}
            </label>
        </div>
    </div>
    {if !empty($postcoder)}
    {$postcoder->drawCheckoutPostcodeHelper($model, "if (\$('#as-shipping:checked')){ \n checkout.copy_address({ data: { address_prefix: 'shipping_address', address_box:'shipping-addresses' } });\n }\n checkout.data_changed('recalculation');")}
    {/if}
    <script>
        tl(['{Info::themeFile('/js/jquery-ui.min.js')}', '{Info::themeFile('/js/address.js')}', '{Info::themeFile('/js/bootstrap-switch.js')}'], function(){
            {\frontend\design\Info::addBoxToCss('autocomplete')}
            $('#{Html::getInputId($model, 'state')}').setStateCountryDependency({
                'country': '#{Html::getInputId($model, 'country')}',
                'url': "{Yii::$app->urlManager->createUrl('account/address-state')}",
            });
            
            $('#{Html::getInputId($model, 'city')}').getCityList({
                'country': '#{Html::getInputId($model, 'country')}',
                'state': '#{Html::getInputId($model, 'state')}',
                'url': "{Yii::$app->urlManager->createUrl('account/address-city')}",
            });

            $('#{Html::getInputId($model, 'postcode')}').getPostcodeList({
                'country': '#{Html::getInputId($model, 'country')}',
                'state': '#{Html::getInputId($model, 'state')}',
                'city': '#{Html::getInputId($model, 'city')}',
                'suburb': '#{Html::getInputId($model, 'suburb')}',
                'url': "{Yii::$app->urlManager->createUrl('account/address-postcode')}",
            });

            $('#{Html::getInputId($model, 'company_vat')}').on('change keyup',function(e){
                checkout.data_changed('check_vat', [{
                    name:'checked_model',value:'{$model->formName()}'
                }])
            })

{if $model->has('COMPANY') && $model->up('CUSTOMS_NUMBER')=='required_company'}
        $('#{Html::getInputId($model, 'company')}').on('change', function(){
            var el = $('#{Html::getInputId($model, 'CUSTOMS_NUMBER')}');
            if ($(this).val()=='') {
               el.addClass('skip-validation');
               el.trigger('change'); // ajax validation error
               //HTML5 jquery validation error
               el.removeClass('required-error');
               el.siblings('.required-message-wrap').hide();
            } else {
               el.removeClass('skip-validation');
            }
        });
        $('#{Html::getInputId($model, 'company')}').trigger('change');
{/if}

            $('#{Html::getInputId($model, 'customs_number')}').on('change keyup',function(e){
                checkout.data_changed('check_customs_number', [{
                    name:'checked_model',value:'{$model->formName()}'
                }])
            })
            
            $('#{Html::getInputId($model, 'as_preferred')}').bootstrapSwitch({
                offText: '{$smarty.const.TEXT_NO}',
                onText: '{$smarty.const.TEXT_YES}',
            });
            
        });
    </script>