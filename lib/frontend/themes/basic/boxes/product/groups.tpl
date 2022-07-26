{$propertyCategoryBefore = 0}
{$propertyCategoryOpened = false}

{foreach $properties as $property}

    {if $property['parent_id'] != $propertyCategoryBefore && $propertyCategoryOpened}
        {$propertyCategoryOpened = false}
        </div></div><!-- .property-category -->
    {/if}
    {if $property['parent_id'] != $propertyCategoryBefore && !$propertyCategoryOpened}
        {$propertyCategoryOpened = true}
        <div class="property-category{if !$propertyCategories[$property['parent_id']]} default{/if}">
            {if $propertyCategories[$property['parent_id']]['properties_name']}
            <div class="property-category-heading">{$propertyCategories[$property['parent_id']]['properties_name']}</div>
            {/if}
            <div class="property-category-content">
    {/if}
    {$propertyCategoryBefore = $property['parent_id']}

<div class="radioBox2 radioBox">
    <div class="radioBoxHead"><span class="title">{$property['properties_name']}:</span> <span class="value">{$property['values'][$property['current_value']]['text']}</span></div>
        {foreach $property['values'] as $value}
        <label {if (!$value['values_color'] && $value['product']['availableWith'])}disabled="disabled" aria-disabled="true"{/if}>
            {*<input type="radio" name="prop_{$property['properties_id']}"{if $value['product']['selected']} checked{/if}>*}
            {if !$value['product']['selected']}<a href="{$value['product']['lazy']['link']}">{/if}
            <div class="containerBlock{if $value['product']['selected']} selected{/if}" title="{$value['product']['name']|escape:'html'}">
              {if !$settings[0].show_prices}
                {$alt=$value['text']|cat:' '|cat:$value['product']['lazy']['price']}
              {else}
                {$alt=$value['text']}
              {/if}

              {if $property['display_as_image']}
                {if $settings[0].show_images == 'products'}
                  {if strlen($value['product']['lazy']['image']) > 0}
                    {$img_style='style="max-width:'|cat:$value['icon_w']|cat:'px; max-height:'|cat:$value['icon_h']|cat:'px;"'}
                    <img src="{$value['product']['lazy']['image']}" title="{$alt|escape:'html'}" alt="{$alt|escape:'html'}" {$img_style}>
					<div class="name">{$value['product']['name']}</div>									
                    {if $settings[0].show_prices}
                      <div class="val2">{$value['product']['lazy']['price']}</div>
                    {/if}
                  {else}
                    <div class="val1" title="{$alt|escape:'html'}" alt="{$alt|escape:'html'}">{$value['text']}</div>
                    {if $settings[0].show_prices}
                      <div class="val2">{$value['product']['lazy']['price']}</div>
                    {/if}
                  {/if}
                {else}
                  <div class="prop-group-swatch">
  {* priority: values_image, values_color, image *}
                  {if strlen($value['values_image']) > 0 || strlen($value['values_color']) > 0 || strlen($value['product']['lazy']['image']) > 0 }
                    {$img_style=''}
                    {if strlen($value['values_image']) > 0}
                      {$src=$app->request->baseUrl|cat:'/images/'|cat:$value['values_image']}
                      {$img_style='style="max-width:'|cat:$value['icon_w']|cat:'px; max-height:'|cat:$value['icon_h']|cat:'px;"'}
                    {elseif strlen($value['values_color']) > 0}
                      {$src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='}
                      {$img_style='style="background-color:'|cat:$value['values_color']|cat:'; width:'|cat:$value['icon_w']|cat:'px; height:'|cat:$value['icon_h']|cat:'px;"'}
                    {else}
                      {$src=$value['product']['lazy']['image']}
                    {/if}
                    {$alt=$value['text']|cat:' '|cat:$value['product']['lazy']['price']}
                    <img src="{$src}" title="{$alt|escape:'html'}" alt="{$alt|escape:'html'}" {$img_style}>
                    {if $settings[0].show_prices}
                      <div class="val2">{$value['product']['lazy']['price']}</div>
                    {/if}
                  {else}
                      <div class="val1" title="{$alt|escape:'html'}" alt="{$alt|escape:'html'}">{$value['text']}</div>
                      {if $settings[0].show_prices}
                        <div class="val2">{$value['product']['lazy']['price']}</div>
                      {/if}
                  {/if}
                  </div>
                {/if}
              {else}
                  <div class="val1" title="{$alt|escape:'html'}" alt="{$alt|escape:'html'}">{$value['text']}</div>
                  {if $settings[0].show_prices}
                    <div class="val2">{$value['product']['lazy']['price']}</div>
                  {/if}
              {/if}
            </div>
<!-- {* Uncomment to show "Available with (i)"
            {if $value['product']['availableWith']}
                <div class="info-message"><span class="info-title">{$smarty.const.TEXT_PRODUCT_GROUP_AVAILABLE_WITH}</span><span class="info-popup"><span>{nl2br($value['product']['availableWith'])}</span></span></div>
            {/if}
*} -->
            {if !$value['product']['selected']}</a>{/if}
        </label>
    {/foreach}
</div>
{foreachelse}
<div class="radioBox2 radioBox">
    <div class="radioBoxHead"><span class="title">{$smarty.const.TEXT_PRODUCTS_GROUPS_VARIANTS}</span></div>
        {foreach $products as $product}
        <label>
            <input type="radio" name="products"{if $product['selected']} checked{/if}>
            {if !$product['selected']}<a href="{$product['lazy']['link']}">{/if}
            <div class="containerBlock" title="{$product['name']}">
                <img src="{$product['lazy']['image']}" title="{$product['name']}" alt="{$product['name']}">
				<div class="title">{$product['name']}</div>
                {* <div class="val2">{$product['lazy']['price']}</div> *}
            </div>
            {if !$product['selected']}</a>{/if}
        </label>
    {/foreach}
</div>
{/foreach}
{if $propertyCategoryOpened}</div></div><!-- .property-category -->{/if}

<!-- {* Uncomment to show "Changed Properties"
{if $changed_properties}
    <div class="changed-properties"><span>{$smarty.const.TEXT_PRODUCT_GROUP_CHANGED_PROPERTIES}</span><br><span>{nl2br($changed_properties)}</span></div>
{/if}
*} -->
