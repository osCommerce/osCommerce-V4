{use class="\yii\helpers\Url"}
{use class="\common\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="texts_block">
                {if {$messages|default:array()|@count} > 0}
                    {foreach $messages as $messageType=> $message}
                        <div class="alert fade in alert-{$messageType}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message}</span>
                        </div>			   
                    {/foreach}
                {/if}
        <div class="widget box box-wrapp-blue filter-wrapp" id="translation-filters">
          <div class="widget-header filter-title">
            <h4>{$smarty.const.TEXT_FILTER}</h4>
            <div class="toolbar no-padding">
              <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
              </div>
            </div>
          </div>
          <div class="widget-content after">

              <div class="row" style="max-width: 1000px">
                  <div class="col-lg-5 m-b-4">
                      <div class="row">
                          <div class="col-sm-4">
                              <label class="label-bold">{$smarty.const.TEXT_SHOW_COLUMNS}</label>
                          </div>
                          <div class="col-sm-8">
                              <div class="show-columns" style="max-width: 300px">
                                  <!--onclick="tableColumn(this)"-->
                                  <div class="filter_checkboxes">
                                      <div class="row">
                                          <div class="col-sm-3 p-r-0 p-l-0">
                                              <input type="checkbox" class="shown sdef uniform" value="0" {if in_array(0, $app->controller->view->key_entity)}checked{/if} data-column ="0">
                                          </div>
                                          <div class="col-sm-9 p-l-0">
                                              {$smarty.const.TABLE_HEADING_LANGUAGE_KEY}
                                          </div>
                                      </div>
                                      <div class="row">
                                          <div class="col-sm-3 p-r-0 p-l-0">
                                              <input type="checkbox" class="shown sdef uniform" value="1"  {if in_array(1, $app->controller->view->key_entity)}checked{/if} data-column ="1">
                                          </div>
                                          <div class="col-sm-9 p-l-0">
                                              {$smarty.const.TABLE_HEADING_LANGUAGE_ENTITY}
                                          </div>
                                      </div>
                                      {foreach $languages as $_key => $_lang}
                                          <div class="row">
                                              <div class="col-sm-3 p-r-0 p-l-0">
                                                  <input type="checkbox" class="shown shownlang uniform" data-language="{$_lang['id']}" value="1" data-column ="{$_lang['data']}" {if $_lang['shown_language'] == 1}checked{/if}>
                                              </div>
                                              <div class="col-sm-9 p-l-0">
                                                  {ucfirst($_lang['code'])}
                                              </div>
                                          </div>
                                      {/foreach}
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-lg-7">
                      <div class="filter_categories column-block filter-box-pl filters-text">
                          <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">



                              <div class="row">
                                  <div class="col-sm-7">
                                      <div class="filter_row row_with_label">
                                          <label>{$smarty.const.TEXT_SEARCH_BY}</label>
                                          <select class="form-control" name="by" onChange="defBy(this.value);">
                                              {foreach $app->controller->view->filters->by as $Item}
                                                  <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                              {/foreach}
                                          </select>
                                      </div>
                                      <div class="filter_row row_with_label">
                                          <label>{$smarty.const.TEXT_BY_LANGUAGE}</label>
                                          <div class="filter_checkboxes">
                                              {foreach $languages as $sl}
                                                  <div class="row">
                                                      <div class="col-sm-3 p-r-0 p-l-0">
                                                          <input type="checkbox" name="sl[]" class="search_l uniform" value="{$sl['id']}" {if $sl['searchable_language'] == 1}checked{/if}>
                                                      </div>
                                                      <div class="col-sm-9 p-l-0">{$sl['name']}</div>
                                                  </div>
                                              {/foreach}
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col-sm-5">
                                      <div class="f_td_group m-b-2">
                                          <input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control search-input"/>
                                      </div>
                                      <label style="width: 100%">
                                          <div class="row">
                                              <div class="col-sm-2 p-r-0">
                                                  <input type="checkbox" name="sensitive" value="1" {if $app->controller->view->filters->sensitive}checked{/if} title="{$smarty.const.TEXT_SENSITIVE}" class="sens-input uniform"/>
                                              </div>
                                              <div class="col-sm-10 p-l-0">
                                                  {$smarty.const.TEXT_SENSITIVE}
                                              </div>
                                          </div>
                                      </label>

                                      <label style="width: 100%">
                                          <div class="row">
                                              <div class="col-sm-2 p-r-0">
                                                  {Html::checkbox('skip_admin', $app->controller->view->filters->skip_admin, ['class' => 'not-admin'])}
                                              </div>
                                              <div class="col-sm-10 p-l-0">
                                                  {$smarty.const.TEXT_SKIP_ADMIN}
                                              </div>
                                          </div>
                                      </label>

                                      <label style="width: 100%">
                                          <div class="row">
                                              <div class="col-sm-2 p-r-0">
                                                  {Html::checkbox('untranslated', $app->controller->view->filters->untranslated, ['class' => 'not-admin'])}
                                              </div>
                                              <div class="col-sm-10 p-l-0">
                                                  {$smarty.const.TEXT_UNTRANSLATED_ONLY}
                                              </div>
                                          </div>
                                      </label>

                                      <label style="width: 100%">
                                          <div class="row">
                                              <div class="col-sm-2 p-r-0">
                                                  {Html::checkbox('unverified', $app->controller->view->filters->unverified, ['class' => 'not-verified'])}
                                              </div>
                                              <div class="col-sm-10 p-l-0">
                                                  {$smarty.const.TEXT_UNVERIFIED_ONLY}
                                              </div>
                                          </div>
                                      </label>

                                  </div>
                              </div>
                              <div class="filters_buttons">
                                  <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                                  <button class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                              </div>
                              <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
                          </form>
                      </div>
                  </div>
              </div>

        </div>
      </div>  
  <div class="order-wrap">
    <!--=== Page Content ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>   	
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-texts-table" checkable_list="{$app->controller->view->checkable_list}" data_ajax="texts/list">

                    <thead>
                        <tr>
                            {foreach $app->controller->view->languagesTable as $tableItem}
                                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                {/foreach}
                        </tr>
                    </thead>

                </table> 

                <div class="btn-bar" style="padding: 10px">
                    <div class="btn-left">
                        <a class="btn btn-primary" href="{Yii::$app->urlManager->createUrl('texts/add')}">{$smarty.const.TEXT_ADD_NEW_KEY}</a>
                        <a class="btn btn-primary export_file" href="{Yii::$app->urlManager->createUrl('texts/export')}">{$smarty.const.TEXT_EXPORT_KEYS}</a>
                        <a class="btn btn-primary import_file" href="{Yii::$app->urlManager->createUrl('texts/import')}">{$smarty.const.TEXT_IMPORT_KEYS}</a>

                    </div>
                    <div class="btn-right">


                    </div>
                </div>
                <form name="importForm">
                </form>
            </div>

        </div>
    </div>

    <!--===Actions ===-->
    <div class="row right_column" id="text_management">		
        <div class="widget box">
            <div class="widget-content fields_style" id="text_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>

    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->

  </div>
</div>
<script type="text/javascript">
    $(function(){
        $(".translate-frontend").tlSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
            onSwitchChange: function(e, status){
                $.get('texts/switch-frontend-translation', { status: status ? 1 : 0})
            }
        });
    })


    var table;
    function defBy(level){
      if (level == 'translation_entity'){ 
        $('input[name=search]').autocomplete({
            source: "{Yii::$app->urlManager->createUrl('texts/entity-list')}",
            minLength: 0,
            autoFocus: true,
            delay: 0,
            appendTo: '.f_td_group',
            open: function (e, ui) {
              if ($(this).val().length > 0) {
                var acData = $(this).data('ui-autocomplete');
                acData.menu.element.find('a').each(function () {
                  var me = $(this);
                  var keywords = acData.term.split(' ').join('|');
                  me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
              }
            }
        })        
      } else {
        if ($('input[name=search]').hasClass('ui-autocomplete-input')) $('input[name=search]').autocomplete('destroy');
      }
      $('input[name=search]').focus();
    }
    
    
    function tableColumn(obj){
    if (typeof table == 'undefined'){
      table = $('.table').DataTable();
    }
      var column = table.column($(obj).data('column'));
      column.visible( ! column.visible() ).draw(false);
    }
    
    function resetFilter() {
        $('select[name="by"]').val('');
        $('input[name="search"]').val('');
        $("#row_id").val(0);
        resetStatement();
        return false;  
    }    

    function onClickEvent(obj, table) {
        table = $(table).DataTable();
        var id = table.row('.selected').index();
        if(typeof id ==='undefined' || id.length == 0) {
          $('#text_management_data .scroll_col').html('');
          return;
        }
        $("#row_id").val(id);
        setFilterState();
        $("#text_management").hide();
        $('#text_management_data .scroll_col').html('');
        var translation_key = $(obj).find('input.cell_identify').val();
        var translation_entity = $(obj).find('input.cell_type').val();
        var selected_entity = '';
        if ($('select[name=by]').val() == 'translation_entity' && $('input[name=search]').val().length > 0){
          selected_entity = $('input[name=search]').val();
        }
        var $_search = window.location.search;
        if ($_search.indexOf('?') != -1){
          $_search = $_search.substr(1);
        }
        
        $.post("{Yii::$app->urlManager->createUrl('texts/actions')}", { 'translation_key' : translation_key, 'translation_entity' : translation_entity, 'selected_entity': selected_entity, 'row': id , 'search' : $_search}, function(data, status){
                if (status == "success") {
                    $('#text_management_data .scroll_col').html(data);
                    $("#text_management").show();
                } else {
                    alert("Request error.");
                }
            },"html");
    }

    function onUnclickEvent(obj, table) {
        //$("#text_management").hide();
        //var event_id = $(obj).find('input.cell_identify').val();
        //var type_code = $(obj).find('input.cell_type').val();

        //$(table).DataTable().draw(false);
    }

    function setFilterState() {
       // var dtable = $('.table').DataTable();
        if (typeof table == 'undefined'){
          table = $('.table').DataTable();
        }        
        /*
        $.each(table.column()[0], function(i, e){
          if ($('input:checkbox[data-column='+e+']').hasClass('sdef')){
            if (table.column(e).visible()){
              $('input:checkbox[data-column='+e+']').prop('checked', true).attr('checked', 'checked');
            } else {
              $('input:checkbox[data-column='+e+']').prop('checked', false).attr('checked', '');
            }          
          }
        });*/
        $.each($(':checkbox.sdef'), function(i, e){
          if ($(e).prop('checked')){
            table.column($(e).data('column')).visible(true);
          } else {
            table.column($(e).data('column')).visible(false);
          }
        })
        orig = $('#filterForm').serialize();
        var url =  window.location.protocol + '//'+window.location.hostname + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({}, '', url);
    }

    function resetStatement() {
        setFilterState();
        table = $('.table').DataTable();
        table.draw( true );
        return false;
    }

    function applyFilter() {
        resetStatement();
        return false;
    }
    
    function translateDelete(translation_key, translation_entity){
      if (confirm('{$smarty.const.TEXT_DELETE_INTRO}')) {
        $.post("{Yii::$app->urlManager->createUrl('texts/delete')}", { 'translation_key' : translation_key, 'translation_entity' : translation_entity }, function(data, status){
          if (status == "success") {
            resetStatement();
          } else {
            alert("Request error.");
          }
        },"html");
      }
      return false;
    }
    
    function translateChangeKey(translation_key, translation_entity){
        $.post("{Yii::$app->urlManager->createUrl('texts/change-key')}", { 'translation_key' : translation_key, 'translation_entity' : translation_entity }, function(data, status){
          if (status == "success") {
            $('#text_management_data .scroll_col').html(data);
            $("#text_management").show();
          } else {
            alert("Request error.");
          }
        },"html");
      return false;
    }
    
    function translateChangeEntity(translation_key, translation_entity){
        $.post("{Yii::$app->urlManager->createUrl('texts/change-entity')}", { 'translation_key' : translation_key, 'translation_entity' : translation_entity }, function(data, status){
          if (status == "success") {
            $('#text_management_data .scroll_col').html(data);
            $("#text_management").show();
          } else {
            alert("Request error.");
          }
        },"html");
      return false;
    }
    
    function changesApply() {
        $.post("{Yii::$app->urlManager->createUrl('texts/apply-changes')}", $('#texts_change_entity').serialize(), function(data, status){
          if (status == "success") {
            resetStatement();
          } else {
            alert("Request error.");
          }
        },"html");
        return false;
    }
    
    $(document).ready(function(){
      //table = $('.table').DataTable();
      $(':checkbox.shown').change(function(){
        if ($(this).data('language') > 0  ){
          obj = this;
          if ($(':checkbox.shownlang:checked').length >0 ){
            
            $.post('{Url::to(["texts/set-shown"])}',
            {
              'id': $(this).data('language'),
              'status': $(this).prop('checked'),
            },
            function(data, success){
             // tableColumn(obj);
              //resetStatement();
              window.location.reload();
            });
          
          } else {
            $(obj).prop('checked', true).attr('checked', 'checked');
          }
        }
      });
      
      $(':checkbox.sdef').change(function(){
          var obj = this;
//        if ($(this).data('language') > 0  ){
            $.post('{Url::to(["texts/set-keyentity"])}',
            {
              'id': $(this).val(),
              'status': $(this).prop('checked'),
            },
            function(data, success){
              tableColumn(obj);
              //resetStatement();
             // window.location.reload();
            });

       // }
      });      

      
      $('.filter_checkboxes div.checker span').click(function(){
        var $val = $(this).find(':checkbox').val();
        var $parent = $(this);
        if ($val > 0 ){
          
          $.post('{Url::to(["texts/set-searchable"])}',
          {
            'id': $val,
            'status': !$($parent).hasClass('checked'),
          },
          function(data, success){
                if($($parent).hasClass('checked')){
                    $($parent).removeClass('checked');
                } else {
                    $($parent).addClass('checked');
                }
            
          });
        //window.location.reload();
        }
      });      
      defBy($('select[name=by]').val());
      setFilterState();
      
      $('.export_file').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading'>{$smarty.const.TEXT_EXPORT_KEYS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
      });
      
      $('.import_file').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading'>{$smarty.const.TEXT_IMPORT_KEYS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
      });
      
    })
</script>