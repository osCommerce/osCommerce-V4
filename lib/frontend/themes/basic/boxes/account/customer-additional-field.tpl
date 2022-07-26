<div class="customer-additional-field {if $field.field_type == 'radio'}field-type-radio{elseif $field.field_type == 'file'}field-type-file{elseif $field.field_type == 'checkbox'}field-type-checkbox{else}field-type-text{/if}">
    {if !$settings[0]['no_label']}
            <label>{$field.title}{if $field.field_type != 'checkbox'}:{/if}{if $field.required}<span class="required">*</span>{/if}</label>
    {/if}

    {if $field.field_type == 'checkbox'}

            <input
                    type="checkbox"
                    name="field[{$field.additional_fields_id}]"
                    data-type="{$field.field_type}"
                    data-group-id="{$field.additional_fields_group_id}"
                    {if $value}checked{/if}
                    {if $field.required}data-required="{$field.title}"{/if}>

    {elseif $field.field_type == 'customer_email'}

            <input
                    type="text"
                    value="{$value}"
                    data-type="{$field.field_type}"
                    data-group-id="{$field.additional_fields_group_id}"
                    disabled>

    {elseif $field.field_type == 'radio'}

        {$foundValue = false}
        {foreach $valuesList as $key => $radioValue}
            <label>
                <input
                        type="radio"
                        name="field[{$field.additional_fields_id}]"
                        value="{$key}"
                        {if isset($value) && (string)$value == (string)$key}checked{$foundValue = true}{/if}
                        {if $field.required}data-required="{$field.title}"{/if}>
                <span>
                    {$radioValue}
                </span>
            </label>
        {/foreach}

        {if $field.option}
            <label class="radio-other-value">
                <input
                        type="radio"
                        name="field[{$field.additional_fields_id}]"
                        value="{if !$foundValue}{$value}{/if}"
                        {if isset($value) && !$foundValue}checked{/if}
                        {*if $field.required}data-required="{$field.title}"{/if*}>
                <span>
                    <input
                            type="text"
                            class="form-control"
                            placeholder="Other"
                            value="{if !$foundValue}{$value}{/if}">
                </span>
            </label>
        {/if}

    {elseif $field.field_type == 'select'}

        <select
                class="form-control"
                name="field[{$field.additional_fields_id}]"
                {if $field.required}data-required="{$field.title}"{/if}
        >
        {foreach $valuesList as $key => $selectValue}
            <option value="{$key}" {if $value == $key}selected{/if}>{$selectValue}</option>
        {/foreach}
        </select>

    {elseif in_array($field.field_type, ['country_id', 'addressbook_country_id'])}

        <select
                class="form-control"
                type="text"
                name="field[{$field.additional_fields_id}]"
                value="{$value}" class="address-field"
                data-type="{$field.field_type}"
                data-iso="{$iso}"
                data-group-id="{$field.additional_fields_group_id}">
            {foreach $countries as $country}
                <option value="{$country.id}" {if $value == $country.id} selected{/if}>{$country.text}</option>
            {/foreach}
        </select>

    {elseif in_array($field.field_type, ['country_code'])}

        <select
                class="form-control"
                type="text"
                name="field[{$field.additional_fields_id}]"
                value="{$value}" class="address-field"
                data-type="{$field.field_type}"
                data-iso="{$iso}"
                data-group-id="{$field.additional_fields_group_id}">

            <option value=""></option>

            {foreach $countries as $country}
                <option value="{$country.countries_iso_code_2}" {if $value == $country.countries_iso_code_2} selected{/if}>{$country.countries_iso_code_2}</option>
            {/foreach}
        </select>

    {elseif in_array($field.field_type, ['gender', 'customer_gender', 'addressbook_gender'])}

        {*foreach \common\helpers\Address::getGendersList() as $key => $radioValue}
            <label>
                <input
                        type="radio"
                        name="field[{$field.additional_fields_id}]"
                        value="{$key}"
                        {if $value == $key}checked{/if}
                        {if $field.required}data-required="{$field.title}"{/if}>
                <span>{$radioValue}</span>
            </label>
        {/foreach*}

    <select
            class="form-control"
            type="text"
            name="field[{$field.additional_fields_id}]"
            data-type="{$field.field_type}"
            data-group-id="{$field.additional_fields_group_id}">
        <option value=""></option>
        {foreach \common\helpers\Address::getGendersList() as $key => $radioValue}
            <option value="{$key}" {if $value == $key} selected{/if}>{$radioValue}</option>
        {/foreach}
    </select>

    {elseif $field.field_type == 'file'}

        <div class="files" data-type="{$field.field_type}" data-field-id="{$field.additional_fields_id}" data-group-id="{$field.additional_fields_group_id}">

            <input
                    name="field[{$field.additional_fields_id}][]"
                    type="hidden"
                    data-type="{$field.field_type}"
                    data-group-id="{$field.additional_fields_group_id}">

        {foreach explode("\n", $value) as $file}
            {if $file}
            <div class="tf-file">
                <div class="title"><a href="{Yii::$app->urlManager->createUrl([$downloadAction, 'file' => $file, 'customer_id' => $customers_id])}" target="_blank">{$file}</a></div>
                <div class="remove"></div>
                <input
                        class="form-control"
                        name="field[{$field.additional_fields_id}][]"
                        value="{$file}"
                        type="hidden"
                        data-type="{$field.field_type}"
                        data-group-id="{$field.additional_fields_group_id}">
            </div>
            {/if}
        {/foreach}
            <div class="tf-file tf-file-first-upload">
                <input
                        class="form-control"
                        name="field[{$field.additional_fields_id}][]"
                        type="file"
                        accept="pdf,png,jpeg,jpg,bmp,gif"
                        data-type="{$field.field_type}"
                        data-group-id="{$field.additional_fields_group_id}">
            </div>
            <div class="add-buttons">
                <span class="btn btn-add">Attach more Files</span>
            </div>
        </div>

    {elseif $field.field_type == 'textarea'}

        <textarea
                class="form-control"
                name="field[{$field.additional_fields_id}]{if $field.option}[]{/if}"
                data-type="{$field.field_type}"
                data-group-id="{$field.additional_fields_group_id}"
                data-field-id="{$field.additional_fields_id}"
                {if $field.required}data-required="{$field.title}"{/if}>{$value}</textarea>

    {elseif $field.field_type == 'vat_number'}

        <select
                class="form-control"
                type="text"
                name="field[{$field.additional_fields_id}][country_code]"
                value="{$value}" class="address-field"
                data-type="{$field.field_type}"
                data-iso="{$iso}"
                data-group-id="{$field.additional_fields_group_id}"
                {if $field.required}data-required="Country VAT"{/if}>>

            <option value=""></option>

            {foreach $countries as $country}
                <option value="{$country.countries_iso_code_2}" {if substr($value, 0, 2) == $country.countries_iso_code_2} selected{/if}>{$country.countries_iso_code_2}</option>
            {/foreach}
        </select>
        <input
                class="form-control"
                name="field[{$field.additional_fields_id}][vat_number]"
                value="{substr($value, 2)}"
                type="text"
                data-type="{$field.field_type}"
                data-group-id="{$field.additional_fields_group_id}"
                data-field-id="{$field.additional_fields_id}"
                {if $field.required}data-required="{$field.title}"{/if}>

    {elseif $field.field_type == 'current_date'}

        <input
                class="form-control"
                name="field[{$field.additional_fields_id}]"
                value="{date("Y-m-d H:i")}"
                type="text"
                data-type="{$field.field_type}"
                data-group-id="{$field.additional_fields_group_id}"
                data-field-id="{$field.additional_fields_id}"
                {if $field.required}data-required="{$field.title}"{/if}>

    {else}

        <input
                class="form-control"
                name="field[{$field.additional_fields_id}]{if $field.option}[]{/if}"
                value="{$value}"
                type="text"
                data-type="{$field.field_type}"
                data-group-id="{$field.additional_fields_group_id}"
                data-field-id="{$field.additional_fields_id}"
                {if $field.required}data-required="{$field.title}"{/if}>

    {/if}

</div>

