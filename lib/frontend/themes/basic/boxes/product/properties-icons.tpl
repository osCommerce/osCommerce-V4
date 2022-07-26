{if {$properties_tree_array|@count} > 0}
  {foreach $properties_tree_array as $key => $property}
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
    {/if}
  {/foreach}
{/if}
