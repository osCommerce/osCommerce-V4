{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

{$_assetsTabsCount = '0' scope="global"}

{function saveStateInLocalStorage}
  {*id_prefix, gloabal $app *}
  <script type="text/javascript">
    $(document).ready(function () {
      $('#{$id_prefix}_ul [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        localStorage.setItem('{$app->controller->id}_{$app->controller->action->id}_{$id_prefix}_lastTab', $(this).attr('href'));
      });
      var lastTab = localStorage.getItem('{$app->controller->id}_{$app->controller->action->id}_{$id_prefix}_lastTab');
      if (lastTab) {
        $('#{$id_prefix}_tabbable_custom [data-bs-target=' + lastTab + ']').tab('show');
      } else {
        $('#{$id_prefix}_tabbable_custom [data-bs-toggle="tab"]:first').tab('show');
      }
    });
  </script>
{/function}
{$fieldSuffix=''}{$idSuffix=''}
{function tabData }
  {* tab-content - common for all types, $tabsType - variable to implement design tweaks.
    id=$id_prefix  tabData=$data fData=$fieldsData *}
  <div class="tab-content">
    {$fieldSuffixParent=$fieldSuffix}
    {$idSuffixParent=$idSuffix}
    {foreach $tabData as $data}
      <div class="tab-pane {if isset($data['additionalCSS'])}{$data['additionalCSS']}{/if}{if {$data@iteration}==1} active{/if} {$data['cssClass']}" id="{$id}_{$data@iteration}">
        {if $tabsType=='lTab'}
        <div class="left-pane">
        {/if}
          {if isset($data['id']) && $data['id'] !== ''}
          {*if $data['id'] !== '' required as id=0 == '' so will be ignored *}
            {$fieldSuffix="`$fieldSuffixParent`[`$data['id']`]"}
            {$idSuffix="`$idSuffixParent`_`$data['id']`"}
          {/if}
          {if isset($data['data']) && $data['data']|default:array()|@count>0}
            {$fieldsData = $data['data']}
          {elseif isset($data['id']) && isset($fData.{$data['id']})}
              {$fieldsData=$fData.{$data['id']}}
          {else}
            {$fieldsData=''}
          {/if}
          {if !isset($defData) || !is_array($defData) } {*//parent's def. data*}
            {$defData = []}
          {/if}
          {if isset($data['def_data']) && is_array($data['def_data']) }{*merge with own def. data*}
            {$fieldsDataDef = array_merge($defData, $data['def_data'])}
          {else}
            {$fieldsDataDef = $defData}
          {/if}
          {if !isset($fieldsData['tabdata']) || $fieldsData['tabdata']|default:array()|@count==0}
            {$fieldsData['tabdata']=$data}
            {if isset($fieldsData['tabdata']['data'])}
              {if $fieldsData['tabdata']['data']|default:array()|@count>0}
                {$fieldsData['tabdata']['data']=[]}
              {/if}
            {else}
              {$fieldsData['tabdata']['data']=[]}
            {/if}
          {/if}
          {$fieldsData['tabdata']['html_id']=$id|cat:"_"|cat:$data@iteration}
          {if is_array($fieldsDataDef) && is_array($fieldsData)}
          {$fieldsData = $fieldsDataDef + $fieldsData}{*array_merge change indexes - foo instead of id array_merge($fieldsDataDef, $fieldsData)*}
          {/if}
          {if isset($data['include']) && $data['include']!=''}
            {include file={$data['include']}}
          {/if}
          {if $data['callback']!=''}
            {call {$data['callback']} data=$fieldsData fieldSuffix=$fieldSuffix idSuffix=$idSuffix}
          {/if}
          {if isset($data['children']) && $data['children']|default:array()|@count>0}
            {if !isset($data['children_callback']) || $data['children_callback']==''}
              {$data['children_callback']=$tabsType}
            {/if}
            {call {$data['children_callback']} id_prefix={"`$id`_`$data@iteration`"} data=$data['children']  fieldSuffix=$fieldSuffix idSuffix=$idSuffix defData=$fieldsDataDef}

          {/if}
          {if isset($data['callback_bottom']) && $data['callback_bottom']!=''}
            {call {$data['callback_bottom']} data=$fieldsData fieldSuffix=$fieldSuffix idSuffix=$idSuffix}
          {/if}
        {if $tabsType=='lTab'}
        </div>
        {/if}
      </div>

    {/foreach }
  </div>
{/function}



{* HORISONTAL SCROLLING TABS *}
{function hTabScrollTabs}
  <!-- ALL PAGES -->
  <div class="tp-all-pages-btn " id="{$id_prefix}_tp_all_pages_btn">
    <div class="tp-all-pages-btn-wrapp">
      <span>{$smarty.const.TEXT_ALL_PAGES}</span>
    </div>
    <div class="tl-all-pages-block" id="{$id_prefix}_tl_all_pages_block">
      <ul class="" id="{$id_prefix}_ul_scr">
        {foreach $tabData as $data}
          <li class="{if {$data@iteration}==1} active{/if} {$data['cssClass']}" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}"><a><span>{$data['title']}</span></a></li>
        {/foreach}
      </ul>
    </div>
  </div>
  <!-- ALL PAGES eof -->
  <!-- Tabs -->
  <ul class="nav nav-tabs nav-tabs-scroll" id="{$id_prefix}_ul">
    {foreach $tabData as $data}
      <li class="{if {$data@iteration}==1} active{/if} {$data['cssClass']}" id="{$id}_{$data@iteration}_li" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}"><a><span>{$data['title']}</span></a></li>
    {/foreach}
  </ul>
  <!-- Tabs eof -->
{/function}

{* function hTabScroll --- params data, id_prefix *}
{function hTabScroll }
  {if $id_prefix==''}
    {assign var="id_prefix" value={"tab`$_assetsTabsCount++`"} }
  {/if}
  <div class="tabbable tabbable-custom" id="{$id_prefix}_tabbable_custom">
    {if count($data) > 1}
      {call hTabScrollTabs id=$id_prefix tabData=$data}
    {/if}
    {if $data|default:array()|@count>0}
      {call tabData tabsType='hTabScroll' id=$id_prefix  tabData=$data fData=$fieldsData}
    {/if}
  </div>
  {if $data|default:array()|@count>0 && (!isset($data[0]['tabsSkipState']) || !$data[0]['tabsSkipState']) }
    {call saveStateInLocalStorage id_prefix=$id_prefix}
  {/if}
{/function}
{* HORISONTAL SCROLLING TABS EOF *}

{* LEFT VERTICAL TABS *}
{function lTabTabs}
  <!-- Tabs -->
  <ul class="nav nav-tabs nav-tabs-left {if !empty($tabData[0]['maxHeight'])}nav-tabs-left-scroll flex-column{/if}" id="{$id_prefix}_ul" {if !empty($tabData[0]['maxHeight'])}style="max-height:{$tabData[0]['maxHeight']}; overflow:auto;"{/if}>
    {foreach $tabData as $data}
      <li class="{if {$data@iteration}==1} active{/if} {$data['cssClass']}" id="{$id}_{$data@iteration}_li" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}"><a><span>{$data['title']}</span></a></li>
          {/foreach}
  </ul>
  <!-- Tabs eof -->
{/function}

{* function lTab --- params data, id_prefix, fieldSuffix *}
{function lTab }
  {if $id_prefix==''}
    {assign var="id_prefix" value={"tab`$_assetsTabsCount++`"} }
  {/if}
  {if $data|default:array()|@count > 0 && !empty($data[0]['aboveTabs'])}
    <div class="nav-tabs-left-above p-t-1">
      {$all_hidden=$data[0]['all_hidden']}
      {include file={$data[0]['aboveTabs']}}
    </div>
  {/if}
  <div class="tabbable tabs-left" id="{$id_prefix}_tabbable_custom">

    {if $data|default:array()|@count > 1}
      {call lTabTabs id=$id_prefix tabData=$data}
    {/if}
    {if $data|default:array()|@count > 0}
      {call tabData tabsType='lTab' id=$id_prefix  tabData=$data fData=$fieldsData}
    {/if}
  </div>
  {if $data|default:array()|@count>0 && (!isset($data[0]['tabsSkipState']) || !$data[0]['tabsSkipState']) }
    {call saveStateInLocalStorage id_prefix=$id_prefix}
  {/if}
{/function}
{* LEFT VERTICAL TABS EOF *}


{* HORISONTAL TABS *}
{function hTabTabs}
  <!-- Tabs -->
  <ul class="nav nav-tabs tabs-h{if !empty($tabData[0]['maxWidth'])}tabs-h-scroll{/if}" id="{$id_prefix}_ul" {if !empty($tabData[0]['maxWidth'])}style="max-width:{$tabData[0]['maxWidth']}; overflow:auto;"{/if}>
    {foreach $tabData as $data}
      <li class="{if {$data@iteration}==1} active{/if} {$data['cssClass']}" id="{$id}_{$data@iteration}_li" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}" data-bs-toggle="tab" data-bs-target="#{$id}_{$data@iteration}"><a><span>{$data['title']}</span></a></li>
    {/foreach}
  </ul>
  <!-- Tabs eof -->
{/function}

{* function hTab --- params data, id_prefix, fieldsData *}
{function hTab }
  {if $id_prefix==''}
    {assign var="id_prefix" value={"tab`$_assetsTabsCount++`"} }
  {/if}
  <div class="tabbable tabbable-custom tabs-h" id="{$id_prefix}_tabbable_custom">
    {if $data|default:array()|@count > 1}
      {call hTabTabs id=$id_prefix tabData=$data}
    {/if}
    {if $data|default:array()|@count > 0}
        {if empty($fieldsData)}
            {$fieldsData=[]}
        {/if}
      {call tabData tabsType='hTab' id=$id_prefix  tabData=$data fData=$fieldsData}
    {/if}
  </div>
  {if $data|default:array()|@count>0 && (!isset($data[0]['tabsSkipState']) || !$data[0]['tabsSkipState']) }
    {call saveStateInLocalStorage id_prefix=$id_prefix}
  {/if}
{/function}
{* HORISONTAL TABS EOF *}

{$_assetsTabsLevels = [] scope="global"}

{function mTab }
  {* transform arrays into global $_assetsTabsLevels - tabs, data*}
  {* tabs [], tabparams[],  fieldsData [][] *}
  {*
  'id' => $languages[$i]['id'],
  'title' => $languages[$i]['image'] . ' ' . $languages[$i]['name'],
  'cssClass' => '', // add to tabs and tab-pane
  'data['tabsSkipState']' => true / empty,false (don't save sellected in local storage)
  'include' => ($i == 0 ? 'test/test.tpl' : ''), // smarty template which will be included before callback
  'callback' => (in_array($i, array(1, 3, 4, 5)) ? 'topContent' : ''), // smarty function which will be called before children tabs , data passed as params params
  'callback_bottom' => (in_array($i, array(1, 2, 3, 6)) ? 'bottomContent' : ''), // smarty function which will be called after children tabs , data passed as params params
  'children_callback' => '', // smarty function which will be called to build children tabs
  'children' => [],
  *}
  {for $i={$tabs|@count-1} to 0 step -1}
    {if $tabs[$i]|default:array()|@count }
      {$d=$tabs[$i]}
    {else}
      {$d=[]}
    {/if}
    {if $tabparams[$i]|default:array()|@count }
      {$p=$tabparams[$i]}
    {else}
      {$p=[]}
    {/if}
    {foreach $d as $k => $v}
      {* add params to each tab*}
      {if is_array($v)}
        {$d[$k] = array_merge($v, $p)}
      {else}
        {$d[$k] = $p}
      {/if}

      {if isset($_assetsTabsLevels[$i]) && is_array($_assetsTabsLevels[$i]) }
        {* save level of tabs in global *}
        {$d[$k] = array_merge($d[$k], $_assetsTabsLevels[$i])}
      {/if}
    {/foreach}
    {$_assetsTabsLevels[$i] = $d}
    {if {$i>0} }
      {$_assetsTabsLevels[$i-1]['children'] = $_assetsTabsLevels[$i]}      
      {$_assetsTabsLevels[$i-1]['children_callback'] = $_assetsTabsLevels[$i][0]['tabs_type']}
    {/if}
  {/for}

    {if isset($tabparams[0]['tabs_type']) && !empty($tabparams[0]['tabs_type']) }
      {$type = $tabparams[0]['tabs_type']}
    {else}
      {$type = 'hTab'}
    {/if}
    {if !isset($id_prefix) || empty($id_prefix) }
      {$id_prefix = 'mTab'}
    {/if}
    {call $type data=$_assetsTabsLevels[0] id_prefix=$id_prefix fieldsData=$fieldsData }

  {** }
  <div class="col-md-4" style="width:50%; float:left"><pre>
    {$type} tabs  {$tabs|default:array()|@count}<br>
  {print_r($tabs)}
    </pre></div>
  <div class="col-md-4" style="width:50%; float:left">tabparams<pre>
  {print_r($tabparams)}
    </pre></div>
  <div class="col-md-4" style="width:100%; float:right">fieldsData<pre>
  {print_r($fieldsData)}
    </pre></div>
    <div class="col-md-12" style="width:100%; float:left">_assetsTabsLevels[0]<pre>
  {ksort($_assetsTabsLevels)}
  {print_r($_assetsTabsLevels[0])}
    </pre></div>
    {**}
{/function}