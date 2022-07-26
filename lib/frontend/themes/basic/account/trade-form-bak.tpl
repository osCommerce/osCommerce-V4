{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
<!--=== Page Content ===-->
<h1>Trade form</h1>

{Html::beginForm('', 'customer_edit', ['id' => 'customers_edit', 'onSubmit' => 'return check_form()'], false)}
{*<form name="customer_edit" id="customers_edit" onSubmit="return check_form();">*}
    <input name="customers_id" value="{$customers_id}" type="hidden">
    <div class="box-wrap">


        <div class="widget box box-no-shadow">
            <div class="widget-header"><h3>{$smarty.const.CUSTOMER_DETAILS}</h3></div>
            <div class="widget-content additional-fields" style="width: 50%">
                <div class="wl-td">
                    <label>{$smarty.const.LIMITED_COMPANY}</label>
                    <input class="form-checkbox" name="field[{$fields.limited_company.id}]" value="1"{if $fields.limited_company.value} checked{/if} type="checkbox">
                </div>
                <div class="wl-td">
                    <label>{$smarty.const.SOLE_TRADER}</label>
                    <input class="form-checkbox" name="field[{$fields.sole_trader.id}]" value="1"{if $fields.sole_trader.value} checked{/if} type="checkbox">
                </div>

            </div>
            <div class="widget-content">
                <div class="w-line-row wd-line-row-2">
                    <div>

                        <div class="widget box box-no-shadow">
                            <div class="widget-header"><h4>{$smarty.const.CATEGORY_COMPANY}</h4></div>
                            <div class="widget-content">
                                <input type="hidden" name="platform_id" value="1">
                                <div class="address-wrapp after">
                                    {foreach $addresses as $address}
                                        <div>
                                            <label class="address-fields{if $customer.customers_default_address_id == $address.id} default-address{/if}">
                                                <input type="radio" name="address" value="{$address.id}">
                                                <b>{$smarty.const.BUSINESS_NAME}:</b> {if $customer.customers_company}{$customer.customers_company}{else}____{/if},<br>
                                                <b>{$smarty.const.ENTRY_TELEPHONE_NUMBER}:</b> {if $customer.customers_telephone}{$customer.customers_telephone}{else}____{/if},<br>
                                                <b>{$smarty.const.ENTRY_EMAIL_ADDRESS}:</b> {if $customer.customers_email_address}{$customer.customers_email_address}{else}____{/if},<br>
                                                <b>{$smarty.const.CATEGORY_ADDRESS}:</b> {if $address.address}{$address.address}{else}____{/if}
                                                <input type="hidden" name="name" value="{$customer.customers_company}"/>
                                                <input type="hidden" name="phone" value="{$customer.customers_telephone}"/>
                                                <input type="hidden" name="email" value="{$customer.customers_email_address}"/>
                                                <input type="hidden" name="postcode" value="{$address.postcode}"/>
                                                <input type="hidden" name="street_address" value="{$address.street_address}"/>
                                                <input type="hidden" name="suburb" value="{$address.suburb}"/>
                                                <input type="hidden" name="city" value="{$address.city}"/>
                                                <input type="hidden" name="state" value="{$address.state}"/>
                                                <input type="hidden" name="country" value="{$address.country_id}"/>
                                            </label>
                                        </div>
                                    {/foreach}
                                    <div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.BUSINESS_NAME}</label>
                                            <input type="text" name="field[{$fields.name.id}]" value="{$fields.name.value}" class="form-control state-control f-name">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>
                                            <input type="text" name="field[{$fields.phone.id}]" value="{$fields.phone.value}" class="form-control state-control f-phone">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}</label>
                                            <input type="text" name="field[{$fields.email.id}]" value="{$fields.email.value}" class="form-control state-control f-email">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-content widget-content-top-border">
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_POST_CODE}</label>
                                            <input type="text" name="field[{$fields.postcode.id}]" value="{$fields.postcode.value}" class="form-control state-control f-postcode">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_STREET_ADDRESS}</label>
                                            <input type="text" name="field[{$fields.street_address.id}]" value="{$fields.street_address.value}" class="form-control state-control f-street_address">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_SUBURB}</label>
                                            <input type="text" name="field[{$fields.suburb.id}]" value="{$fields.suburb.value}" class="form-control state-control f-suburb">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_CITY}</label>
                                            <input type="text" name="field[{$fields.city.id}]" value="{$fields.city.value}" class="form-control state-control f-city">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_STATE}</label>
                                            <input type="text" name="field[{$fields.state.id}]" value="{$fields.state.value}" class="form-control f-state">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_COUNTRY}</label>
                                            <select name="field[{$fields.country.id}]" class="form-control f-country">
                                                {foreach $countries as $country}
                                                    <option value="{$country.id}" {if $fields.country.value == $country.id} selected{/if}>{$country.text}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div>
                        <div class="widget box box-no-shadow">
                            <div class="widget-header"><h4>{$smarty.const.OWNERS_DETAILS}</h4></div>
                            <div class="widget-content">
                                <input type="hidden" name="platform_id" value="1">
                                <div class="address-wrapp after">
                                    {foreach $addresses as $address}
                                        <div>
                                            <label class="address-fields{if $customer.customers_default_address_id == $address.id} default-address2{/if}">
                                                <input type="radio" name="address1" value="{$address.id}">
                                                <b>{$smarty.const.TEXT_NAME}:</b> {$customer.customers_firstname} {$customer.customers_lastname},<br>
                                                <b>{$smarty.const.ENTRY_TELEPHONE_NUMBER}:</b> {if $customer.customers_telephone}{$customer.customers_telephone}{else}____{/if},<br>
                                                <b>{$smarty.const.ENTRY_EMAIL_ADDRESS}:</b> {if $customer.customers_email_address}{$customer.customers_email_address}{else}____{/if},<br>
                                                <b>{$smarty.const.CATEGORY_ADDRESS}:</b> {if $address.address}{$address.address}{else}____{/if}
                                                <input type="hidden" name="owners_firstname" value="{$customer.customers_firstname}"/>
                                                <input type="hidden" name="owners_lastname" value="{$customer.customers_lastname}"/>
                                                <input type="hidden" name="owners_phone" value="{$customer.customers_telephone}"/>
                                                <input type="hidden" name="owners_postcode" value="{$address.postcode}"/>
                                                <input type="hidden" name="owners_street_address" value="{$address.street_address}"/>
                                                <input type="hidden" name="owners_suburb" value="{$address.suburb}"/>
                                                <input type="hidden" name="owners_city" value="{$address.city}"/>
                                                <input type="hidden" name="owners_state" value="{$address.state}"/>
                                                <input type="hidden" name="owners_country" value="{$address.country_id}"/>
                                            </label>
                                        </div>
                                    {/foreach}
                                    <div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_FIRST_NAME}</label>
                                            <input type="text" name="field[{$fields.owners_firstname.id}]" value="{$fields.owners_firstname.value}" class="form-control state-control f-owners_firstname">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_LAST_NAME}</label>
                                            <input type="text" name="field[{$fields.owners_lastname.id}]" value="{$fields.owners_lastname.value}" class="form-control state-control f-owners_lastname">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>
                                            <input type="text" name="field[{$fields.owners_phone.id}]" value="{$fields.owners_phone.value}" class="form-control state-control f-owners_phone">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-content widget-content-top-border">
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_POST_CODE}</label>
                                            <input type="text" name="field[{$fields.owners_postcode.id}]" value="{$fields.owners_postcode.value}" class="form-control state-control f-owners_postcode">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_STREET_ADDRESS}</label>
                                            <input type="text" name="field[{$fields.owners_street_address.id}]" value="{$fields.owners_street_address.value}" class="form-control state-control f-owners_street_address">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_SUBURB}</label>
                                            <input type="text" name="field[{$fields.owners_suburb.id}]" value="{$fields.owners_suburb.value}" class="form-control state-control f-owners_suburb">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_CITY}</label>
                                            <input type="text" name="field[{$fields.owners_city.id}]" value="{$fields.owners_city.value}"class="form-control state-control f-owners_city">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-line-row wd-line-row-2">
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_STATE}</label>
                                            <input type="text" name="field[{$fields.owners_state.id}]" value="{$fields.owners_state.value}" class="form-control f-owners_state">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="wl-td">
                                            <label>{$smarty.const.ENTRY_COUNTRY}</label>
                                            <select name="field[{$fields.owners_country.id}]" class="form-control f-owners_country">
                                                {foreach $countries as $country}
                                                    <option value="{$country.id}" {if $fields.owners_country.value == $country.id} selected{/if}>{$country.text}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-line-row wd-line-row-2 additional-fields">
                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_FAX_NUMBER}</label>
                            <input class="form-control" name="field[{$fields.fax.id}]" value="{$fields.fax.value}" type="text">
                        </div>
                    </div>
                </div>
                <div class="w-line-row wd-line-row-2 additional-fields">
                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.NATURE_OF_BUSINESS}</label>
                            <input class="form-control" name="field[{$fields.nature_business.id}]" value="{$fields.nature_business.value}" type="text">
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                    tl(function(){
                        $('.address-fields').on('click', function(){
                            $('input[type="hidden"]', this).each(function(){
                                $('input.f-' + $(this).attr('name') + ', select.f-' + $(this).attr('name')).val($(this).val())
                            })
                        })
                    })
                </script>

            </div>
        </div>


        <div class="widget box box-no-shadow">
            <div class="widget-header"><h3>{$smarty.const.TEXT_DISCOUNT}</h3></div>
            <div class="widget-content additional-fields">

                <div class="w-line-row wd-line-row-2 additional-fields">

                    <div class="checkboxes">
                        <div class="wl-td">
                            <label>{$smarty.const.SALE_OR_RETURN}</label>
                            <input class="form-checkbox" name="field[{$fields.sale_return.id}]" {if $fields.sale_return.value} checked{/if} type="checkbox">
                        </div>
                    </div>

                    <div class="checkboxes">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_FIRM}</label>
                            <input class="form-checkbox" name="field[{$fields.firm.id}]" {if $fields.firm.value} checked{/if} type="checkbox">
                        </div>
                    </div>

                    <div class="checkboxes">
                        <div class="wl-td">
                            <label>{$smarty.const.CASH_WITH_ORDER}</label>
                            <input class="form-checkbox" name="field[{$fields.cash_with_order.id}]" {if $fields.cash_with_order.value} checked{/if} type="checkbox">
                        </div>
                    </div>

                    <div class="checkboxes">
                        <div class="wl-td">
                            <label>{$smarty.const.CASH_CARRY}</label>
                            <input class="form-checkbox" name="field[{$fields.cash_carry.id}]" {if $fields.cash_carry.value} checked{/if} type="checkbox">
                        </div>
                    </div>

                </div>

            </div>
        </div>


        <div class="widget box box-no-shadow">
            <div class="widget-header"><h3>{$smarty.const.BANK_ACCOUNT_DETAILS}</h3></div>
            <div class="widget-content additional-fields">

                <div class="w-line-row wd-line-row-2 additional-fields">

                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.BANK_NAME}</label>
                            <input class="form-control" name="field[{$fields.bank_name.id}]" value="{$fields.bank_name.value}" type="text">
                        </div>
                    </div>
                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.CATEGORY_ADDRESS}</label>
                            <input class="form-control" name="field[{$fields.bank_address.id}]" value="{$fields.bank_address.value}" type="text">
                        </div>
                    </div>
                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.ACCOUNT_NO}</label>
                            <input class="form-control" name="field[{$fields.bank_account_no.id}]" value="{$fields.bank_account_no.value}" type="text">
                        </div>
                    </div>

                </div>

            </div>
        </div>


        <div class="widget box box-no-shadow">
            <div class="widget-header"><h3>{$smarty.const.TRADE_REFERENCES}</h3></div>
            <div class="widget-content additional-fields">

                    <div class="w-line-row wd-line-row-2 additional-fields">

                        <div>
                            <div class="widget box box-no-shadow">
                                <div class="widget-header"><h4>{$smarty.const.TRADE_REFERENCE} 1</h4></div>
                                <div class="widget-content additional-fields">
                                    <div class="w-line-row additional-fields">
                                        <div>
                                            <div class="wl-td" style="margin-bottom: 10px">
                                                <label>{$smarty.const.TABLE_TEXT_NAME}</label>
                                                <input class="form-control" name="field[{$fields.trade_name_1.id}]" value="{$fields.trade_name_1.value}" type="text">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="wl-td" style="margin-bottom: 10px">
                                                <label>{$smarty.const.CATEGORY_ADDRESS}</label>
                                                <input class="form-control" name="field[{$fields.trade_address_1.id}]" value="{$fields.trade_address_1.value}" type="text">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="wl-td" style="margin-bottom: 10px">
                                                <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>
                                                <input class="form-control" name="field[{$fields.trade_phone_1.id}]" value="{$fields.trade_phone_1.value}" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="widget box box-no-shadow">
                                <div class="widget-header"><h4>{$smarty.const.TRADE_REFERENCE} 2</h4></div>
                                <div class="widget-content additional-fields">
                                    <div class="w-line-row additional-fields">
                                        <div>
                                            <div class="wl-td" style="margin-bottom: 10px">
                                                <label>{$smarty.const.TABLE_TEXT_NAME}</label>
                                                <input class="form-control" name="field[{$fields.trade_name_2.id}]" value="{$fields.trade_name_2.value}" type="text">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="wl-td" style="margin-bottom: 10px">
                                                <label>{$smarty.const.CATEGORY_ADDRESS}</label>
                                                <input class="form-control" name="field[{$fields.trade_address_2.id}]" value="{$fields.trade_address_2.value}" type="text">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="wl-td" style="margin-bottom: 10px">
                                                <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>
                                                <input class="form-control" name="field[{$fields.trade_phone_2.id}]" value="{$fields.trade_phone_2.value}" type="text">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

            </div>
        </div>


        <div class="widget box box-no-shadow">
            <div class="widget-header"><h3>{$smarty.const.TEXT_DECLARATION}</h3></div>
            <div class="widget-content additional-fields">

                <div class="w-line-row wd-line-row-2 additional-fields">

                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.NAME_IN_FULL}</label>
                            <input class="form-control" name="field[{$fields.name_in_full.id}]" value="{$fields.name_in_full.value}" type="text">
                        </div>
                    </div>
                    <div>
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_POSITION}</label>
                            <input class="form-control" name="field[{$fields.position.id}]" value="{$fields.position.value}" type="text">
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
    <div class="buttons">
        <div class="left-buttons">
            {if !$create}
            <a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.CANCEL}</a>
            {/if}
        </div>
        <div class="right-buttons">
            {if $create}
                <input type="hidden" name="create" value="1"/>
                <button class="btn">{$smarty.const.IMAGE_BUTTON_CONTINUE}</button>
            {else}
                {if \common\helpers\Acl::checkExtensionAllowed('TradeForm')}
                <a href="{$app->urlManager->createUrl('account/trade-acc')}?customers_id={$customers_id}" target="_blank" class="btn-1 btn-pdf">PDF</a>
                <button class="btn btn-confirm">{$smarty.const.TEXT_WISHLIST_SAVE}</button>
                {/if}
            {/if}
        </div>
    </div>
{Html::endForm()}
<!-- /Add Customer -->
<!-- /Page Content -->
<script>
    function backStatement() {
        window.history.back();
        return false;
    }
    function check_form() {

        var customers_edit = $('#customers_edit');
        var values = customers_edit.serializeArray()
        values = values.concat(
                $('input[type=checkbox]:not(:checked)', customers_edit).map(function() {
                    console.log(this.name);
                    return { "name": this.name, "value": 0}
                }).get()
        );

        $.post("{$app->urlManager->createUrl('trade-form/trade-form-submit')}", values, function(data, status){
            if (status == "success") {
                {if $create}
                window.location.href = '{$app->urlManager->createUrl(['account', 'page_name' => 'created_success'])}';
                {else}
                location.reload();
                {/if}
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    tl(function(){

        $(window).resize(function(){
            $('.cbox-right .box-no-shadow').css('min-height', $('.cbox-left').height() - 20);
        });
        $(window).resize();

        if (
                $('.f-name').val() == '' &&
                $('.f-phone').val() == '' &&
                $('.f-email').val() == '' &&
                $('.f-postcode').val() == '' &&
                $('.f-street_address').val() == '' &&
                $('.f-suburb').val() == '' &&
                $('.f-city').val() == '' &&
                $('.f-state').val() == '' &&
                $('.f-country').val() == ''
        ) {
            $('.default-address').trigger('click');
        }

        if (
                $('.f-owners_firstname').val() == '' &&
                $('.f-owners_lastname').val() == '' &&
                $('.f-owners_phone').val() == '' &&
                $('.f-owners_postcode').val() == '' &&
                $('.f-owners_street_address').val() == '' &&
                $('.f-owners_suburb').val() == '' &&
                $('.f-owners_city').val() == '' &&
                $('.f-owners_state').val() == '' &&
                $('.f-owners_country').val() == ''
        ) {
            $('.default-address2').trigger('click');
        }

        var customers_edit = $('#customers_edit');
        $('.btn-confirm', customers_edit).hide();
        customers_edit.on('change', function(){
            $('.btn-confirm', customers_edit).show();
            $('.btn-pdf', customers_edit).hide();
        });
        $('input', customers_edit).on('keyup', function(){
            $('.btn-confirm', customers_edit).show();
            $('.btn-pdf', customers_edit).hide();
        })
    })
</script>