{if $settings.add_link}
    <a href="{$product.link}">
{/if}

{if $settings.element == 'name'}

    <span class="name">{$product.products_name}</span>

{elseif $settings.element == 'image' || $settings.element == 'image_med' || $settings.element == 'image_lrg'}

    <span class="image"><img src="{$product.image}" alt="{$product.image_alt}" title="{$product.image_title}"></span>

{elseif $settings.element == 'description'}

    <span class="description">{$product.products_description}</span>

{elseif $settings.element == 'short_description'}

    <span class="short_description">{$product.products_description_short}</span>

{elseif $settings.element == 'price'}

    <span class="price">
        {if $product.old != ''}<span class="old">{$product.old}</span>{/if}
        {if $product.special != ''}<span class="special">{$product.special}</span>{/if}
        {if $product.price != ''}<span class="current">{$product.price}</span>{/if}
    </span>

{elseif $settings.element == 'model'}

    <span class="model">{$product.model}</span>

{elseif $settings.element == 'stock'}

    <span class="stock">
        <span class="{$product.stock.text_stock_code}">
            <span class="{$product.stock.stock_code}-icon">&nbsp;</span>{$product.stock.stock_indicator_text}
        </span>
    </span>

{elseif $settings.element == 'properties'}

    {if is_array($product.properties) && count($product.properties) > 0}
        <div class="properties">
            {foreach $product.properties as $key => $property}
                {if {$property['values']|@count} > 0}
                    {if $property['properties_type'] == 'flag' && $property['properties_image']}
                        <div class="property-image">
                            {if $property['values'][1] == 'Yes'}
                                <span class="hover-box">
                  <img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}">
                  <span class="hover-box-content">
                    <strong>{$property['properties_name']}</strong>
                      {\common\helpers\Properties::get_properties_description($property['properties_id'], $languages_id)}
                  </span>
                </span>
                            {else}
                                <span class="disable">
                  <img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}">
                </span>
                            {/if}
                        </div>
                    {else}
                        <div class="property">
                            <strong>{$property['properties_name']}<span class="colon">:</span></strong>
                            {foreach $property['values'] as $value_id => $value}{if $value@index > 0}, {/if}<span>{$value}</span>{/foreach}
                        </div>
                    {/if}
                {/if}
            {/foreach}
        </div>
    {/if}

{/if}

{if $settings.add_link}
    </a>
{/if}