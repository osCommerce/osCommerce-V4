<input type="hidden" name="global_id" id="global_id" value="{$global_id}">
{foreach $app->controller->view->documents as $documents_id => $documents}
<div class="widget box box-no-shadow">
    <div class="widget-header">
        <h4>{$documents['title']}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse">
                    <i class="icon-angle-down"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="widget-content" id="documents-type-{$documents['id']}">
      <div class="documents-list">
        {foreach $documents['docs'] as $docs}
          <div class="docs-item">
            <div class="doc-handle"></div>
            <div class="filename">
              {$docs['filename']}

              <input type="hidden" name="products_documents_id[{$documents['id']}][]" value="{$docs['products_documents_id']}">
              <input type="hidden" name="document_types_id[{$documents['id']}][]" value="{$docs['document_types_id']}" class="document_types_id">
              <input type="hidden" name="is_link[{$documents['id']}][]" value="{$docs['is_link']}" class="is_link">
              <input type="hidden" name="filename[{$documents['id']}][]" value="{$docs['filename']}">
              <input type="hidden" name="sort_order[{$documents['id']}][]" value="{$docs['sort_order']}" class="sort_order">
            </div>
            <div class="remove">{$smarty.const.UNLINK_FROM_PRODUCT}</div>
            <div class="edit"></div>
            <div class="file-title">


              <div class="tabbable tabbable-custom">
                  {if count($languages) > 1}
                <div class="nav nav-tabs">

                  {foreach $languages as $language}
                    <div{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$docs.products_documents_id}_{$language.id}"><a>{$language.logo} {$language.name}</a></div>
                  {/foreach}

                </div>
                  {/if}
                <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                  {foreach $languages as $language}

                    <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$docs.products_documents_id}_{$language.id}" data-language="{$language.id}">
                      <label><strong>{$smarty.const.TITLE_SHOW_FRONTEND}<span class="colon">:</span></strong></label>

                      <input type="text" name="title[{$language['id']}][{$documents['id']}][]" value="{$docs['title'][$language['id']]}" class="form-control" placeholder="{$smarty.const.TABLE_HEADING_TITLE}">

                    </div>
                  {/foreach}
                  <script type="text/javascript">
                    $('.upload').uploads();
                  </script>
                </div>

                <div class="item-buttons">
                  <span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span>
                  <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                </div>

              </div>


            </div>
          </div>
        {/foreach}
      </div>
    </div>
</div>
{/foreach}
<script type="text/javascript">
  $(function(){
    $('#filterForm').trigger('list-change')
  })
</script>


