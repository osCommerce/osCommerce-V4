{*
property:
      values_id,
      properties_id,
      language_id,
      values_text,
      values_number,
      values_number_upto,
      values_alt,
      values_seo_page_name,
      link
*}
<div class="properties-listing">
  {foreach $properties as $property}
    <div class="item">
      <a href="{$property.link}" class="item-link">
        {if $type == 'file'}
          <span class="image" style="background-image: url('{$app->request->baseUrl}/images/{$property.values_text}')"></span>
          <span class="name">
            <span class="title">{$property.values_alt}</span>
          </span>
        {else}
          <span class="name">
            <span class="title">{$property.values_text}</span>
          </span>
        {/if}
      </a>
    </div>
  {/foreach}
</div>