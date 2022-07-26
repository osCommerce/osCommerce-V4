{use class="frontend\design\Info"}
{if $settings[0].customers_data == 'name'}

    <div class="customers-data customers-data-name">{$customer.customers_firstname} {$customer.customers_lastname}</div>

{elseif $settings[0].customers_data == 'dob'}

    {if in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register']) && $customer.customers_dob != '1975-07-24 00:00:00'}
        <div class="customers-data customers-data-dob">{\common\helpers\Date::date_long($customer.customers_dob)}</div>
    {else}
        {$empty = true}
    {/if}

{elseif $settings[0].customers_data == 'email'}

    <div class="customers-data customers-data-email">{$customer.customers_email_address}</div>

{elseif $settings[0].customers_data == 'telephone'}

    {if $customer.customers_telephone}
        <div class="customers-data customers-data-telephone">{$customer.customers_telephone}</div>
    {else}
        {$empty = true}
    {/if}

{/if}

{if $empty && !Info::isAdmin()}
    <script>
        tl(function(){
            {if $settings[0].hide_parents == 1}
            $('#box-{$id}').hide()
            {elseif $settings[0].hide_parents == 2}
            $('#box-{$id}').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 3}
            $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 4}
            $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
            {/if}
        })
    </script>
{/if}