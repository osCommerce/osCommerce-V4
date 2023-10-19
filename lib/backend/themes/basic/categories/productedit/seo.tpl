{use class="\common\helpers\Categories"}
{$gTypeTree = \common\helpers\Categories::get_category_tree()}

<div class="tabbable tabbable-custom">
{if count($app->controller->view->platforms) > 1}
    <ul class="nav nav-tabs platform-tabs">
        {foreach $app->controller->view->platforms as $platform}
            <li{if $platform->platform_id == $app->controller->view->def_platform_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_9_{$platform->platform_id}"><a class="flag-span" data-id="{$platform->platform_id}"><span>{$platform->platform_name}</span>
            </a></li>
        {/foreach}
    </ul>
    <div class="tab-content platform-name-contents">
    {foreach $app->controller->view->platforms as $platform}
        <div id="tab_9_{$platform->platform_id}" class="tab-pane {if $platform->platform_id == $app->controller->view->def_platform_id}active{/if}" data-owner-id="{$platform->platform_id}">
            <div class="tabbable tabbable-custom">
            {call tabSeo platform_id = $platform->platform_id}
            </div>
        </div>
    {/foreach}
    </div>
{else}
    {call tabSeo platform_id = $app->controller->view->def_platform_id}
{/if}
</div>
{function tabSeo}
    {if count($languages) > 1}
    <ul class="nav nav-tabs">
      {foreach $languages as $lKey => $lItem}
        <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_9_{$platform_id}_{$lItem['id']}"><a class="flag-span">{$lItem['image']}<span>{$lItem['name']}</span></a></li>
      {/foreach}
    </ul>
  {/if}
  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $languages as $lKey => $lItem}
      <div class="tab-pane tab-seo{if $lKey == 0} active{/if}" id="tab_9_{$platform_id}_{$lItem['id']}">
        <div class="edp-line">
          <label>{$smarty.const.TEXT_SEO_PAGE_NAME}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_seo_page_name]" value="{if isset($pDescription[$platform_id][$lKey]['products_seo_page_name'])}{$pDescription[$platform_id][$lKey]['products_seo_page_name']|escape}{/if}" class="form-control form-control-small" />
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_NO_INDEX}</label>
          <input name="pDescription[{$platform_id}][{$lItem['id']}][noindex_option]" value="1" class="check_bot_switch_on_off" type="checkbox" {if isset($pDescription[$platform_id][$lKey]['noindex_option']) && $pDescription[$platform_id][$lKey]['noindex_option']}checked{/if} />
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_NO_FOLLOW}</label>
          <input name="pDescription[{$platform_id}][{$lItem['id']}][nofollow_option]" value="1" class="check_bot_switch_on_off" type="checkbox" {if isset($pDescription[$platform_id][$lKey]['nofollow_option']) && $pDescription[$platform_id][$lKey]['nofollow_option']}checked{/if} />
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_CANONICAL}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][rel_canonical]" value="{if isset($pDescription[$platform_id][$lKey]['rel_canonical'])}{$pDescription[$platform_id][$lKey]['rel_canonical']|escape}{/if}" class="form-control form-control-small" />
          {if isset($dItem['rel_canonical'])}{$dItem['rel_canonical']}{/if}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_SELF_SERVICE}</label>
          <textarea name="pDescription[{$platform_id}][{$lItem['id']}][products_self_service]" class="form-control form-control-small" wrap="soft" cols="70" rows="5">{if isset($pDescription[$platform_id][$lKey]['products_self_service'])}{$pDescription[$platform_id][$lKey]['products_self_service']}{/if}</textarea>
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_PAGE_TITLE}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_head_title_tag]" value="{if isset($pDescription[$platform_id][$lKey]['products_head_title_tag'])}{$pDescription[$platform_id][$lKey]['products_head_title_tag']|escape}{/if}" class="form-control form-control-small products_head_title_tag" />
          <input type="checkbox" name="pDescription[{$platform_id}][{$lItem['id']}][overwrite_head_title_tag]" value="1"{if (isset($pDescription[$platform_id][$lKey]['overwrite_head_title_tag']) && $pDescription[$platform_id][$lKey]['overwrite_head_title_tag']) || !$pInfo->products_id} checked{/if} /> {$smarty.const.TEXT_OVERWRITE_PAGE_TITLE}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_HEADER_DESCRIPTION}</label>
          <textarea name="pDescription[{$platform_id}][{$lItem['id']}][products_head_desc_tag]" class="form-control form-control-small text-dox-01 products_head_desc_tag" wrap="soft" cols="70" rows="5">{if isset($pDescription[$platform_id][$lKey]['products_head_desc_tag'])}{$pDescription[$platform_id][$lKey]['products_head_desc_tag']}{/if}</textarea>
          <input type="checkbox" name="pDescription[{$platform_id}][{$lItem['id']}][overwrite_head_desc_tag]" value="1"{if (isset($pDescription[$platform_id][$lKey]['overwrite_head_desc_tag']) && $pDescription[$platform_id][$lKey]['overwrite_head_desc_tag']) || !$pInfo->products_id} checked{/if} /> {$smarty.const.TEXT_OVERWRITE_HEADER_DESCRIPTION}
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_H1_TAG}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_h1_tag]" value="{if isset($pDescription[$platform_id][$lKey]['products_h1_tag'])}{$pDescription[$platform_id][$lKey]['products_h1_tag']|escape}{/if}" class="form-control form-control-small" />
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_H2_TAG}</label>
          <div class="h-teg-table">
          <span id="products_h2_tag-{$platform_id}-{$lItem['id']}">{if isset($pDescription[$platform_id][$lKey]['products_h2_tag'])}{foreach explode("\n", $pDescription[$platform_id][$lKey]['products_h2_tag']) as $value}<span class="h-teg-row"><input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_h2_tag][]" value="{$value|escape}" class="form-control form-control-small" /><span class="del-pt del-tag"></span></span>{/foreach}{/if}</span><span onclick="addInput('products_h2_tag-{$platform_id}-{$lItem['id']}', '{htmlspecialchars('<span class="h-teg-row"><input type="text" name="pDescription['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][products_h2_tag][]" value="" class="form-control form-control-small" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span>
        </div>
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_H3_TAG}</label>
          <div class="h-teg-table">
          <span id="products_h3_tag-{$platform_id}-{$lItem['id']}">{if isset($pDescription[$platform_id][$lKey]['products_h3_tag'])}{foreach explode("\n", $pDescription[$platform_id][$lKey]['products_h3_tag']) as $value}<span class="h-teg-row"><input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_h3_tag][]" value="{$value|escape}" class="form-control form-control-small" /><span class="del-pt del-tag"></span></span>{/foreach}{/if}</span><span onclick="addInput('products_h3_tag-{$platform_id}-{$lItem['id']}', '{htmlspecialchars('<span class="h-teg-row"><input type="text" name="pDescription['|cat:$platform_id|cat:']['|cat:$lItem['id']|cat:'][products_h3_tag][]" value="" class="form-control form-control-small" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span>
          </div>
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_IMAGE_ALT_TAG_MASK}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_image_alt_tag_mask]" value="{if isset($pDescription[$platform_id][$lKey]['products_image_alt_tag_mask'])}{$pDescription[$platform_id][$lKey]['products_image_alt_tag_mask']|escape}{/if}" class="form-control form-control-small" />
        </div>
        <div class="edp-line">
          <label>{$smarty.const.TEXT_IMAGE_TITLE_TAG_MASK}</label>
          <input type="text" name="pDescription[{$platform_id}][{$lItem['id']}][products_image_title_tag_mask]" value="{if isset($pDescription[$platform_id][$lKey]['products_image_title_tag_mask'])}{$pDescription[$platform_id][$lKey]['products_image_title_tag_mask']|escape}{/if}" class="form-control form-control-small" />
        </div>
          {foreach \common\helpers\Hooks::getList('categories/productedit', 'seo-tab') as $filename}
              {include file=$filename}
          {/foreach}

<!-- Moved to SeoRedirectsNamed {*
        <div class="edp-line">
          <label>{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</label>
          <input type="text" name="pDescription[{$platform_id}][products_old_seo_page_name]" value="{$pInfo->products_old_seo_page_name}" class="form-control seo-input-field old-seo-page">
          <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
          {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
          <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
          {/if}
          <script>
          $(document).ready(function(){
            $('body').on('click', "#tab_9_{$platform_id}_{$lItem['id']} .icon-home", function(){
              $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
            });
          {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
            $('body').on('click', '#tab_9_{$platform_id}_{$lItem['id']} .icon-external-link', function(){
              $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
            });
          {/if}
          })
          </script>
        </div>
*} -->
        {if \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')}
           {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderProduct($pInfo->products_id, $lItem['id'], $platform_id)}
        {/if}
      </div>
    {/foreach}
  </div>
{/function}
<script>
  $(document).ready(function(){
      $('.products_head_title_tag').limitValue('title');
      $('.products_head_desc_tag').limitValue('description');

    $('.old-seo-page').keyup(function(e){ var value = e.target.value; $.each($('.old-seo-page'), function(i,spage){ if (e.target != spage){ $(spage).val(value); } }) })
  })
  function addInput (id, input) {
    $('#' + id).append(input);
  }
  $('body').on('click', '.del-pt.del-tag', function(){
    $(this).parent().remove();
  });
</script>