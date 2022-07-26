{use class="\common\helpers\Html"}
{*use class="frontend\design\Info"*}
<div class=" w-line-row-2">
    {assign var=re1 value='.{'}
    {assign var=re2 value='}'}
    {Html::activeHiddenInput($model, 'address_book_id')}
    
    <div class="columns">
        {if $model->has('GENDER')}
            <div class="col-full genders-title">
                <div class="">{field_label const="ENTRY_GENDER" configuration=$model->get('GENDER')}</div>
                {Html::activeRadioList($model, 'gender', $model->getGendersList(), ['unselect' => null])}                    
            </div>
        {/if}
        {if $model->has('FIRSTNAME')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_FIRST_NAME" configuration=$model->get('FIRSTNAME')}</span>
                    {if $model->has('FIRSTNAME', false)}
                        {Html::activeTextInput($model, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'firstname', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('LASTNAME')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_LAST_NAME" configuration=$model->get('LASTNAME')}</span>
                    {if $model->has('LASTNAME', false)}
                        {Html::activeTextInput($model, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'lastname', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
		{if $model->has('COMPANY')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_COMPANY" configuration=$model->get('COMPANY')}</span>
                    {if $model->has('COMPANY', false)}
                        {Html::activeTextInput($model, 'company', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_COMPANY_ERROR}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'company', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('COMPANY_VAT')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_BUSINESS" configuration=$model->get('COMPANY_VAT')}</span>
                    {if $model->has('COMPANY_VAT', false)}
                        {Html::activeTextInput($model, 'company_vat', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_VAT_ID_ERROR}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'company_vat', ['class' => 'form-control'])}
                    {/if}
                    <span class="company_vat_status"></span>
                </label>
            </div>
        {/if}
        {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
        {elseif $type=='shipping'}
            {$customerModel = $manager->getCustomerContactForm()}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</span>
                    {Html::activeInput('email', $customerModel, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email", 'class' => 'form-control'])}
                </label>
            </div>
            {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                <div class="col-2">
                    <label>
                        <span>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</span>
                        {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
                            {Html::activeTextInput($customerModel, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}", 'class' => 'form-control'])}
                        {else}
                            {Html::activeTextInput($customerModel, 'telephone',['class' => 'form-control'])}
                        {/if}
                    </label>
                </div>
            {/if}
        {/if}
    </div>
    <div class="columns">
        {if $model->has('COUNTRY')}
            <div class="col-2">
                <label>                    
					{if sizeof($model->getAllowedCountries()) > 1}
					<span>{field_label const="ENTRY_COUNTRY" configuration=$model->get('COUNTRY')}</span>
                    {Html::activeDropDownList($model, 'country', $model->getAllowedCountries(), ['data-required' => "{$smarty.const.ENTRY_COUNTRY_ERROR}", 'class' => 'form-control', 'data-iso' => $model->getAllowedCountriesISO()])}
					{else}
					{Html::activeHiddenInput($model, 'country', ['class' => 'form-control'])}
					{/if}
                </label>
				
            </div>
        {/if}
        <div class="col-2"></div>
		{if $model->has('POSTCODE')}
            <div class="col-3 address-wrap postcode-wrap">
                <label>
                    <span>{field_label const="ENTRY_POST_CODE" configuration=$model->get('POSTCODE')}</span>
                    {if $model->has('POSTCODE', false)}
                        {Html::activeTextInput($model, 'postcode', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH)}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'postcode', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
		{if $model->has('CITY')}
            <div class="col-3 address-wrap city-wrap">
                <label>
                    <span>{field_label const="ENTRY_CITY" configuration=$model->get('CITY')}</span>
                    {if $model->has('CITY', false)}
                        {Html::activeTextInput($model, 'city', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH)}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'city', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('STREET_ADDRESS')}
            <div class="col-3">
                <label>
                    <span>{field_label const="ENTRY_STREET_ADDRESS" configuration=$model->get('STREET_ADDRESS')}</span>
                    {if $model->has('STREET_ADDRESS', false)}
                        {Html::activeTextInput($model, 'street_address', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH)}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'street_address', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
        {if $model->has('SUBURB')}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_SUBURB" configuration=$model->get('SUBURB')}</span>
                    {if $model->has('SUBURB', false)}
                        {Html::activeTextInput($model, 'suburb', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_SUBURB_ERROR}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'suburb', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
        
        {if $model->has('STATE')}
            <div class="col-2 address-wrap state-wrap">
                <label>
                    <span>{field_label const="ENTRY_STATE" configuration=$model->get('STATE')}</span>
                    {if $model->has('STATE', false)}
                        {Html::activeTextInput($model, 'state', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH)}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($model, 'state', ['class' => 'form-control'])}
                    {/if}
                </label>
            </div>
        {/if}
        
        
        
        
        <div class="col-full">
            <span class="required">*Required fields</span>
        </div>
    </div>
</div>
    {if !empty($postcoder)}
    {$postcoder->drawEditorPostcodeHelper($model, "order.dataChanged($('#checkoutForm'), 'recalculation');")}
    {/if}
    <script>   
    $(document).ready(function(){
        let holder = "{$holder} ";
        
        $(holder + 'input[name="{$model->formName()}[state]"]').setStateCountryDependency({
            'country': holder + ':input[name="{$model->formName()}[country]"]',
            'url': "{Yii::$app->urlManager->createUrl('editor/address-state')}",
            'appendto': holder +' .state-wrap'
        });

        $(holder + 'input[name="{$model->formName()}[city]"]').getCityList({
            'country': holder + ':input[name="{$model->formName()}[country]"]',
            'state': holder + 'input[name="{$model->formName()}[state]"]',
            'url': "{Yii::$app->urlManager->createUrl('editor/address-city')}",
            'appendto': holder +' .city-wrap'
        });

        $(holder + 'input[name="{$model->formName()}[postcode]"]').getPostcodeList({
            'country': holder + ':input[name="{$model->formName()}[country]"]',
            'state': holder + 'input[name="{$model->formName()}[state]"]',
            'city': holder + 'input[name="{$model->formName()}[city]"]',
            'suburb': holder + 'input[name="{$model->formName()}[suburb]"]',
            'url': "{Yii::$app->urlManager->createUrl('editor/address-postcode')}",
            'appendto': holder +' .postcode-wrap'
        });

        $('#{Html::getInputId($model, 'company_vat')}').keyup(function(e){
            order.dataChanged( $('#checkoutForm'), 'check_vat', [{
                name:'checked_model',value:'{$model->formName()}'
            }])
        })
    })

    </script>