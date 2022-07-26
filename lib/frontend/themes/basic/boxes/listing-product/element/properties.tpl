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
                        {foreach $property['values'] as $value_id => $value}
                            {if $value@index > 0}, {/if}<span>{$value}</span>
                        {/foreach}
                    </div>
                {/if}
            {/if}
        {/foreach}
    </div>
{/if}