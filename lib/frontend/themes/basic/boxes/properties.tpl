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
  {$count = 1}
  {foreach $properties as $property}
    <div class="item{if $count == $count_properties} hide_other{/if}">
      {if !$products_id}<a href="{$property.link}" class="item-link">{/if}
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
      {if !$products_id}</a>{/if}
    </div>
    {$count = $count + 1}
  {/foreach}
</div>
{if sizeof($properties) > $count_properties}
<div class="more_links"><a href="#"><span class="more">{$smarty.const.TEXT_MORE}</span><span class="less" style="display:none;">{$smarty.const.TEXT_LESS}</span></a></div>
<script>
tl(function(){
    $('.more_links a').on('click', function(){
        $(this).toggleClass('lessItem');
        $('.properties-listing').toggleClass('showItem');
        return false;
    })
})
</script>
{/if}
