{use class="\common\helpers\Html"}
{*use class="frontend\design\Info"*}
<div class="address-book-form">
    {assign var=re1 value='.{'}
    {assign var=re2 value='}'}
    {Html::activeHiddenInput($model, 'address_book_id')}
    
    <div class="row me-0">
        {if $model->has('GENDER')}
            <div class="col-12 mb-2 genders-title">
                <label>{field_label const="ENTRY_GENDER" configuration=$model->get('GENDER')}</label>
                {Html::activeRadioList($model, 'gender', $model->getGendersList(), ['unselect' => null])}                    
            </div>
        {/if}
        {if $model->has('FIRSTNAME')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_FIRST_NAME" configuration=$model->get('FIRSTNAME')}</label>
                {if $model->has('FIRSTNAME', false)}
                    {Html::activeTextInput($model, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'firstname', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        {if $model->has('LASTNAME')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_LAST_NAME" configuration=$model->get('LASTNAME')}</label>
                {if $model->has('LASTNAME', false)}
                    {Html::activeTextInput($model, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'lastname', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        {if $model->has('EMAIL_ADDRESS')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_EMAIL_ADDRESS" configuration=$model->get('EMAIL_ADDRESS')}</label>
                {if $model->has('EMAIL_ADDRESS', false)}
                    {Html::activeTextInput($model, 'email_address', ['data-pattern' => "email", 'data-required' => "{sprintf($smarty.const.ENTRY_EMAIL_ADDRESS_ERROR, $smarty.const.ENTRY_EMAIL_ADDRESS_CHECK_ERROR)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'email_address', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        {if $model->has('TELEPHONE')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration=$model->get('TELEPHONE')}</label>
                {if $model->has('TELEPHONE', false)}
                    {Html::activeTextInput($model, 'telephone', ['data-pattern' => "email", 'data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_ERROR, $smarty.const.ENTRY_TELEPHONE_CHECK_ERROR)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'telephone', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
		{if $model->has('COMPANY')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_COMPANY" configuration=$model->get('COMPANY')}</label>
                {if $model->has('COMPANY', false)}
                    {Html::activeTextInput($model, 'company', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_COMPANY_ERROR}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'company', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        {if $model->has('COMPANY_VAT')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_BUSINESS" configuration=$model->get('COMPANY_VAT')}</label>
                {if $model->has('COMPANY_VAT', false)}
                    {Html::activeTextInput($model, 'company_vat', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_VAT_ID_ERROR}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'company_vat', ['class' => 'form-control'])}
                {/if}
                <span class="company_vat_status"></span>
            </div>
        {/if}
        {*if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
        {elseif $type=='shipping'}
            {$customerModel = $manager->getCustomerContactForm()}
            <div class="col-6 mb-2">
                    <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
                    {Html::activeInput('email', $customerModel, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email", 'class' => 'form-control'])}
            </div>
            {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                <div class="col-6 mb-2">
                    <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
                        {Html::activeTextInput($customerModel, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}", 'class' => 'form-control'])}
                    {else}
                        {Html::activeTextInput($customerModel, 'telephone',['class' => 'form-control'])}
                    {/if}
                </div>
            {/if}
        {/if*}
        {if $model->has('COUNTRY')}
            <div class="col-6 mb-2">
                {if sizeof($model->getAllowedCountries()) > 1}
                <label>{field_label const="ENTRY_COUNTRY" configuration=$model->get('COUNTRY')}</label>
                {Html::activeDropDownList($model, 'country', $model->getAllowedCountries(), ['data-required' => "{$smarty.const.ENTRY_COUNTRY_ERROR}", 'class' => ' form-select ', 'data-iso' => $model->getAllowedCountriesISO()])}
                {else}
                {Html::activeHiddenInput($model, 'country', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
		{if $model->has('POSTCODE')}
            <div class="col-6 mb-2 address-wrap postcode-wrap">
                <label>{field_label const="ENTRY_POST_CODE" configuration=$model->get('POSTCODE')}</label>
                {if $model->has('POSTCODE', false)}
                    {Html::activeTextInput($model, 'postcode', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'postcode', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
		{if $model->has('CITY')}
            <div class="col-6 mb-2 address-wrap city-wrap">
                <label>{field_label const="ENTRY_CITY" configuration=$model->get('CITY')}</label>
                {if $model->has('CITY', false)}
                    {Html::activeTextInput($model, 'city', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'city', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        {if $model->has('STREET_ADDRESS')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_STREET_ADDRESS" configuration=$model->get('STREET_ADDRESS')}</label>
                {if $model->has('STREET_ADDRESS', false)}
                    {Html::activeTextInput($model, 'street_address', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'street_address', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        {if $model->has('SUBURB')}
            <div class="col-6 mb-2">
                <label>{field_label const="ENTRY_SUBURB" configuration=$model->get('SUBURB')}</label>
                {if $model->has('SUBURB', false)}
                    {Html::activeTextInput($model, 'suburb', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_SUBURB_ERROR}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'suburb', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        
        {if $model->has('STATE')}
            <div class="col-6 mb-2 address-wrap state-wrap">
                <label>{field_label const="ENTRY_STATE" configuration=$model->get('STATE')}</label>
                {if $model->has('STATE', false)}
                    {Html::activeTextInput($model, 'state', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH)}", 'class' => 'form-control'])}
                {else}
                    {Html::activeTextInput($model, 'state', ['class' => 'form-control'])}
                {/if}
            </div>
        {/if}
        
        
        
        
        <div class="col-12 required-title">
            <span class="required">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
        </div>
    </div>
</div>
    {if !empty($postcoder)}
    {$postcoder->drawEditorPostcodeHelper($model, "order.dataChanged($('#checkoutForm'), 'recalculation');")}
    {/if}
    <script>   
    $(document).ready(function(){
        let holder = "{$holder} ";

        $(holder + '*[name="{$model->formName()}[state]"]').setStateCountryDependency({
            'country': holder + '*[name="{$model->formName()}[country]"]',
            'url': "{Yii::$app->urlManager->createUrl('editor/address-state')}",
            'appendto': holder +' .state-wrap'
        });

        $(holder + 'input[name="{$model->formName()}[city]"]').getCityList({
            'country': holder + '*[name="{$model->formName()}[country]"]',
            'state': holder + 'input[name="{$model->formName()}[state]"]',
            'url': "{Yii::$app->urlManager->createUrl('editor/address-city')}",
            'appendto': holder +' .city-wrap'
        });

        $(holder + 'input[name="{$model->formName()}[postcode]"]').getPostcodeList({
            'country': holder + '*[name="{$model->formName()}[country]"]',
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
        });

        $('.address-book-form input').validate();
    })

    </script>