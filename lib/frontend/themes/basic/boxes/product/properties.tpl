<div class="product-properties">
  {if isset($settings.name_h) && $settings.name_h}
      {$hno = '<'|cat:$settings.name_h|cat:'>'}
      {$hnc = '</'|cat:$settings.name_h|cat:'>'}
  {else}
      {$hno = ''}
      {$hnc = ''}
  {/if}
  {if isset($settings.value_h) && $settings.value_h}
      {$hvo = '<'|cat:$settings.value_h|cat:'>'}
      {$hvc = '</'|cat:$settings.value_h|cat:'>'}
  {else}
      {$hvo = ''}
      {$hvc = ''}
  {/if}

  {if $products_data.category}
    <ul class="properties-table">
      <li class="propertiesName">
        <strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_CATEGORY}{$hnc}</strong>
      </li>
      <li class="propertiesValue">
          <a href="{$products_data.category_link}" title="{$products_data.category|escape:'html'}">
            {$hvo}{$products_data.category}{$hvc}
          </a>
      </li>
    </ul>
  {/if}

  {if $products_data.manufacturers_name && (!isset($settings.show_manufacturer) or $settings.show_manufacturer != 'no')}
  <ul class="properties-table">
    <li class="propertiesName"><strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_MANUFACTURER}{$hnc}</strong></li>
    <li class="propertiesValue">
      {if $products_data.manufacturers_link}
      <a href="{$products_data.manufacturers_link}" title="{$products_data.manufacturers_name|escape:'html'}"><span itemprop="brand">{$hvo}{$products_data.manufacturers_name}{$hvc}</span></a>
      {else}
      <span class="propertiesBrand" itemprop="brand">{$hvo}{$products_data.manufacturers_name}{$hvc}</span>
      {/if}
    </li>
  </ul>
  {/if}

  {if !(isset($settings.show_sku) && $settings.show_sku == 'no')}
  <ul class="properties-table js_prop-block{if !$products_data.products_model} js-hide{/if}">
    <li class="propertiesName"><strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_MODEL}{$hnc}</strong></li>
    <li class="propertiesValue js_prop-products_model" itemprop="sku">{$hvo}{$products_data.products_model}{$hvc}</li>
  </ul>
  {/if}

  {if $products_data.products_ean && $settings.show_ean != 'no'}
    <ul class="properties-table{if !$products_data.products_ean} js-hide{/if}">
      <li class="propertiesName js_prop-block"><strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_EAN}{$hnc}</strong></li>
      <li class="propertiesValue js_prop-products_ean" itemprop="gtin8">{$hvo}{$products_data.products_ean}{$hvc}</li>
    </ul>
  {/if}
  {if $products_data.products_isbn && $settings.show_isbn != 'no'}
    <ul class="properties-table{if !$products_data.products_isbn} js-hide{/if}">
      <li class="propertiesName js_prop-block"><strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_ISBN}{$hnc}</strong></li>
      <li class="propertiesValue js_prop-products_isbn" itemprop="isbn">{$hvo}{$products_data.products_isbn}{$hvc}</li>
    </ul>
  {/if}
  {if $products_data.products_asin && $settings.show_asin != 'no'}
    <ul class="properties-table{if !$products_data.products_asin} js-hide{/if}">
      <li class="propertiesName js_prop-block"><strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_ASIN}{$hnc}</strong></li>
      <li class="propertiesValue js_prop-products_asin" itemprop="asin">{$hvo}{$products_data.products_asin}{$hvc}</li>
    </ul>
  {/if}
  {if $products_data.products_upc && $settings.show_upc != 'no'}
    <ul class="properties-table{if !$products_data.products_upc} js-hide{/if}">
      <li class="propertiesName js_prop-block"><strong class="propertiesName-strong">{$hno}{$smarty.const.TEXT_UPC}{$hnc}</strong></li>
      <li class="propertiesValue js_prop-products_upc">{$hvo}{$products_data.products_upc}{$hvc}</li>
    </ul>
  {/if}
{if is_array($properties_tree_array) && $properties_tree_array|@count > 0}

  <div itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
{foreach $properties_tree_array as $key => $property}
  <ul id="property-{$property['properties_id']}" class="property-ul {$property['properties_type']}" itemprop="value" itemscope itemtype="http://schema.org/PropertyValue">
    <li class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}">
      <strong class="propertiesName-strong">
          {if !empty($property['properties_image'])}<img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}" width="48px;">{/if}
        <span{if !empty($property['properties_color'])} style="color: {$property['properties_color']};"{/if} class="propertiesName-span" itemprop="name">{$hno}{$property['properties_name']}{$hnc}</span>
      </strong>
    </li>
    <li class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}">
    {if {$property['values']|@count} > 0}
      {foreach $property['values'] as $value_id => $value}
        <div class="sel_pr_values">
          {if $settings.clickable_property_filters == '' && strlen($path) > 0}<a href="{tep_href_link('catalog/index', 'cPath='|cat:$path|cat:'&pr'|cat:$property['properties_id']|cat:'[]='|cat:$value_id)}">{elseif $settings.clickable_property_filters != 'no'}<a href="{tep_href_link('catalog/all-products', 'pr'|cat:$property['properties_id']|cat:'[]='|cat:$value_id)}">{/if}
          {if !empty($property['images'][$value_id])}<img src="{$app->request->baseUrl}/images/{$property['images'][$value_id]}" alt="{$value}" width="48px;">{/if}<span{if !empty($property['colors'][$value_id])} style="color: {$property['colors'][$value_id]};"{/if} class="propertiesValue-span" id="value-{$value_id}" itemprop="value">{$hvo}{$value}{$hvc}{if isset($property['extra_values'][$value_id])} {$property['extra_values'][$value_id]}{/if}</span>
          {if $settings.clickable_property_filters != 'no'}</a>{/if}
        </div>
      {/foreach}
    {/if}
    </li>
  </ul>
{/foreach}
  </div>

{/if}
</div>
