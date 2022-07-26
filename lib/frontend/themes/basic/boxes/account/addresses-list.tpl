{use class="frontend\design\Info"}
<input type="hidden" name="group_id" value="{$settings[0].group}"/>
{foreach $addressArray as $address}
    <div class="addsess-suggestion-item">
        <label class="address-fields{if $customer.customers_default_address_id == $address.id} default-address{/if}">
            <input type="radio" name="address" value="{$address.id}">
            <div class="name">
                <b>{$smarty.const.TEXT_NAME}:</b>
                {$customer.customers_firstname} {$customer.customers_lastname},
            </div>
            <div class="company">
                <b>{$smarty.const.BUSINESS_NAME}:</b>
                {if $customer.customers_company}{$customer.customers_company}{else}____{/if},
            </div>
            <div class="phone">
                <b>{$smarty.const.ENTRY_TELEPHONE_NUMBER}:</b>
                {if $customer.customers_telephone}{$customer.customers_telephone}{else}____{/if},
            </div>
            <div class="email">
                <b>{$smarty.const.ENTRY_EMAIL_ADDRESS}:</b>
                {if $customer.customers_email_address}{$customer.customers_email_address}{else}____{/if},
            </div>
            <div class="address">
                <b>{$smarty.const.CATEGORY_ADDRESS}:</b>
                {if $address.address}{$address.address}{else}____{/if}
            </div>
            <input type="hidden" name="firstname" value="{$customer.customers_firstname}"/>
            <input type="hidden" name="lastname" value="{$customer.customers_lastname}"/>
            <input type="hidden" name="company" value="{$customer.customers_company}"/>
            <input type="hidden" name="phone" value="{$customer.customers_telephone}"/>
            <input type="hidden" name="email" value="{$customer.customers_email_address}"/>
            <input type="hidden" name="postcode" value="{$address.postcode}"/>
            <input type="hidden" name="street_address" value="{$address.street_address}"/>
            <input type="hidden" name="suburb" value="{$address.suburb}"/>
            <input type="hidden" name="city" value="{$address.city}"/>
            <input type="hidden" name="state" value="{$address.state}"/>
            <input type="hidden" name="country_id" value="{$address.country_id}"/>

        </label>
    </div>
{/foreach}

<script type="text/javascript">
    tl(function(){

        var box = $('#box-{$id}');

        var group = $('input[name="group_id"]', box).val();

        var $groupInputs = $('input[data-group-id="' + group + '"]')

        if (!$groupInputs.filter('input[data-type="firstname"], input[data-type="lastname"]').length){
            $('.name', box).remove()
        }
        if (!$groupInputs.filter('input[data-type="company"]').length){
            $('.company', box).remove()
        }
        if (!$groupInputs.filter('input[data-type="phone"]').length){
            $('.phone', box).remove()
        }
        if (!$groupInputs.filter('input[data-type="email"]').length){
            $('.email', box).remove()
        }


        $('input[type="radio"]', box).on('change', function(){
            var $addressFields = $(this).closest('.address-fields');
            $groupInputs.each(function () {
                var type = $(this).data('type');
                var val = $('input[name="' + type + '"]', $addressFields).val()
                $(this).val(val)
            })
        })
    })
</script>