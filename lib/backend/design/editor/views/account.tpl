{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<div class="">
    <div class="popup-heading">Add a new customer</div>

    {assign var=re1 value='.{'}
    {assign var=re2 value='}'}
    {Html::beginForm($url, 'post', ['name' => 'customer_create', 'id' => 'customer_create'])}
    <div class="popup-content">
        <div class="status-box row">
            <div class="col-4">
                <span class="status">{$smarty.const.ENTRY_ACTIVE}</span>
                {Html::activeCheckbox($contact, 'status', ['class' => 'check_bot_switch_on_off'])}
            </div>
            {if \common\helpers\Acl::checkExtensionAllowed('CustomerCode', 'allowed')}
                {\common\extensions\CustomerCode\CustomerCode::renderErpFields($contact)}
            {/if}
            <div class="col platform-column">
                <label class="platform">{field_label const="TABLE_HEADING_PLATFORM" required_text=""}</label>
                {Html::activeDropDownList($contact, 'platform_id', $platforms, ['class' => 'form-select'])}
            </div>
            {if $showGroup}                        
                <div class="col">
                    <label class="group">{$smarty.const.ENTRY_GROUP}</label>
                    {Html::activeDropDownList($contact, 'group', $groups, ['class' => 'form-select', 'prompt' => $smarty.const.PULL_DOWN_DEFAULT])}
                </div>  
            {/if}
        </div>
        <div class="create-or-wrap after new-customer-details">
            <div class="new-customer messages-box"></div>
                <div class="row">
                    <div class="col personal-box">
                        <div class="widget box box-no-shadow">
                            <div class="widget-header widget-header-personal"><h4>{$smarty.const.CATEGORY_PERSONAL}</h4></div>
                            <div class="widget-content ">
                                <div class="row">
                                    {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                                        <div class="col-12 mb-2 genders-title">
                                            <label>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</label>
                                            {Html::activeRadioList($contact, 'gender', $contact->getGenderList(), ['unselect' => null])}
                                        </div>
                                    {/if}
                                    {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                                        <div class="col-6 mb-2">
                                            <label>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
                                            {if in_array(ACCOUNT_FIRSTNAME, $contact->getRequired())}
                                                {Html::activeTextInput($contact, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                                            {else}
                                                {Html::activeTextInput($contact, 'firstname', ['class' => 'form-control'])}
                                            {/if}
                                        </div>
                                    {/if}
                                    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                                        <div class="col-6 mb-2">
                                            <label>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
                                            {if in_array(ACCOUNT_LASTNAME, $contact->getRequired())}
                                                {Html::activeTextInput($contact, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                                            {else}
                                                {Html::activeTextInput($contact, 'lastname', ['class' => 'form-control'])}
                                            {/if}
                                        </div>
                                    {/if}
                                    {if in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])}
                                        <div class="col-6 mb-2">
                                            <label>{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"}</label>
                                            {assign var="options" value = ['class' => "form-control datepicker dobTmp", 'autocomplete' => 'off']}
                                            {if ACCOUNT_DOB == 'required_register'} {$options['data-required'] = "{$smarty.const.ENTRY_DATE_OF_BIRTH_ERROR}"}{/if}
                                            {Html::activeTextInput($contact, 'dobTmp', $options)}
                                            {Html::activeHiddenInput($contact, 'dob', ['class' => 'dob-res'])}
                                        </div>
                                    {/if}

                                    <div class="col-6 mb-2">
                                        <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
                                        {Html::activeTextInput($contact, 'email_address', ['class' => 'form-control', 'data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email"])}
                                    </div>
                                    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                                    <div class="col-6 mb-2">
                                        <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                                        {Html::activeTextInput($contact, 'telephone', ['class' => 'form-control'])}
                                    </div>
                                    {/if}
                                    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                                    <div class="col-6 mb-2">
                                        <label>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
                                        {Html::activeTextInput($contact, 'landline', ['class' => 'form-control'])}
                                    </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3"><span class="required">* Required fields</span></div>
                    </div>
                    {if $manager->isShippingNeeded()}
                    <div class="col">
                        <div class="widget box box-no-shadow">
                            <div class="widget-header widget-header-address"><h4>Shipping Address</h4></div>
                            <div class="widget-content new-shipping-address-box">
                            {$manager->render('Address', ['manager' => $manager, 'model' => $shipping, 'holder' => '.new-shipping-address-box'])}
                            </div>
                        </div>
                    </div>
                    {/if}
                    <div class="col">
                        <div class="widget box box-no-shadow">
                            <div class="widget-header widget-header-billing"><h4>
                            Billing Address
                            {if $manager->isShippingNeeded()}
                                <div class="same-address"><input type="checkbox" name="ship_as_bill" value="1" id="as-shipping"{*if $same*} checked {*/if*}/>same as Shipping Address</div>
                            {/if}
                            </h4>
                            </div>
                            <div class="widget-content new-billing-address-box">
                            <div class="billing-content-disabled"></div>
                            {$manager->render('Address', ['manager' => $manager, 'model' => $billing, 'holder' => '.new-billing-address-box'])}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    <div class="popup-buttons">
        <div class="btn-left">
            <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
        </div>
        <div class="btn-right">
            {Html::submitButton($smarty.const.TEXT_CREATE, ['class' => 'btn btn-confirm'])}
        </div>
    </div>
    {Html::endForm()}
    <script>
        $(document).ready(function(){
            $(".check_bot_switch_on_off").bootstrapSwitch(
                {
                    onText: "{$smarty.const.SW_ON}",
                    offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                }
            );
            
            let adresses = $('#customer_create');
            let fields = $('input, select', adresses);
            
            fields.validate();
            
            fields.on('change', { address_prefix: '{$type}_address', address_box:'{$type}-addresses' } , function(event){            
                if ($('.new-customer-details [name=ship_as_bill]:checkbox').prop('checked')){
                    order.copyAddress({ data: { address_prefix: 'shipping_address', address_box:'new-shipping-address-box' } }, $('.add-customer-box'), 'new-');
                }
            })
                        
            $('.dobTmp').datepicker({
                startView: 1,
                format: '{$smarty.const.DATE_FORMAT_DATEPICKER}yy',
                //language: 'current',
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                onSelect: function (value, e){
                    let month = parseInt(e.selectedMonth)+1;
                    try{
                        var date = new Date(month+'/'+e.selectedDay+'/'+e.selectedYear);
                        $('.dob-res').val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
                    } catch(error){
                        console.log(error);
                    }
                    return false;
                }
            }).removeClass('required-error').next('.required-message-wrap').remove();
            
            $('.new-customer-details [name=ship_as_bill]:checkbox').change(function(e){
                if ($(this).prop('checked')){
                    $('.new-billing-address-box').prepend('<div class="billing-content-disabled"></div>');
                } else {
                    $('.billing-content-disabled').remove();
                }
            })
            
            $('form#customer_create').submit(function(e){
                $('form#customer_create input').trigger('change');
                if(!$(e.target).has('.required-message-wrap').length){
                    e.preventDefault();                
                    $.post("{$url}", $(e.target).serializeArray(), function(data){
                        if (data.hasOwnProperty('error')){
                            $('.new-customer.messages-box').html(data.messages);
                        } else {
                            order.showMessage(data.messages, true);
                            setTimeout(function(){ window.location.reload(); }, 100);
                        }
                    }, 'json');
                }
                return false;
            });
            
        })
    </script>
</div>