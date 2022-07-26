

    {if !$multifield}
    <div class="customer-additional-field-show {if $field.field_type == 'radio'}field-type-radio{elseif $field.field_type == 'file'}field-type-file{elseif $field.field_type == 'checkbox'}field-type-checkbox{else}field-type-text{/if}">
        {if !$settings[0]['no_label']}
            <label>{$field.title}{if $field.field_type != 'checkbox'}:{/if}{if $field.required}<span class="required">*</span>{/if}</label>
        {/if}
    <span>
    {/if}

    {if $field.field_type == 'checkbox'}

        {if $value}{$checked}{else}{$notChecked}{/if}

    {elseif $field.field_type == 'customer_email'}

        {$value}

    {elseif $field.field_type == 'radio'}

        {$foundValue = false}
        {foreach $valuesList as $key => $radioValue}
            <div class="radio-item">
                <span class="radio-item-checkbox">{if isset($value) && (string)$value == (string)$key}{$foundValue = true}{$checked}{else}{$notChecked}{/if}</span>
                {$radioValue}
            </div>
        {/foreach}

        {if $field.option && !$foundValue && $value}
            <div class="radio-item">
                <span class="radio-item-checkbox">{$checked}</span>
                {$value}
            </div>
        {/if}

    {elseif $field.field_type == 'select'}

        {foreach $valuesList as $key => $selectValue}
            {if $value == $key}{$selectValue}{/if}
        {/foreach}

    {elseif in_array($field.field_type, ['country_id', 'addressbook_country_id'])}

            {foreach $countries as $country}
                {if $value == $country.id}{$country.text}{/if}
            {/foreach}

    {elseif in_array($field.field_type, ['gender', 'customer_gender', 'addressbook_gender'])}

        {foreach \common\helpers\Address::getGendersList() as $key => $radioValue}
            {if $value == $key}{$radioValue}{/if}
        {/foreach}

    {elseif $field.field_type == 'file'}

        {foreach explode("\n", $value) as $file}
            {if $file}
            <div class="tf-file">
                <a href="{Yii::$app->urlManager->createUrl([$downloadAction, 'file' => $file, 'customer_id' => $customers_id])}" target="_blank">{$file}</a>
            </div>
            {/if}
        {/foreach}

    {elseif $field.field_type == 'textarea'}

        {$value}

    {else}

        {if $multifield}
            {foreach $multifield as $_field}
        <div class="customer-additional-field-show field-type-text">
                {if !$settings[0]['no_label']}
                    <label>{$field.title}{if $field.field_type != 'checkbox'}:{/if}{if $field.required}<span class="required">*</span>{/if}</label>
                {/if}
                <span class="multifield">{$_field}</span>
        </div>
            {/foreach}
        {else}
            {$value}
        {/if}

    {/if}
        {if !$multifield}
        </span>
    </div>
    {/if}


