{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
<!--=== Page Content ===-->
<div id="texts_management_data">
<!--===Customers List ===-->
         <div class="alert fade in" style="display:none;">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <span id="message_plce"></span>
         </div>   	
			  {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $messageType=>$message}
              <div class="alert fade in alert-{$messageType}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}
		<div class="widget box box-wrapp-blue filter-wrapp widget-closed">
          <div class="widget-header filter-title">
            <h4>{$smarty.const.TEXT_FILTER}</h4>
            <div class="toolbar no-padding">
              <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
              </div>
            </div>
          </div>		
		  <div class="widget-content after">
		  <form id="filterForm" name="filterForm" action="{Url::to(['texts/edit-search'])}" method="post">
				<div class="column-block filter-box filters-text">

                    <div class="filter_block after">
                        <div class="filter_left">
                            <div class="filter_row row_with_label">
                                <label>{$smarty.const.TEXT_SEARCH_BY}</label>
                                <select class="form-control" name="by" onChange="defBy(this.value);">
                                    {foreach $app->controller->view->filters->by as $Item}
                                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="filter_right">
                            <div class="filter_row filter_disable">
                                <div class="f_td_group f_td_group-pr">
                                    <input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control search-input"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 after">
                            <div class="filter_row row_with_label">
                                <label>{$smarty.const.TEXT_BY_LANGUAGE}</label>
                                <div class="filter_checkboxes">
                                    {foreach $languages as $sl}
                                        <div style="padding-right: 5px;">
                                            <input type="checkbox" name="sl[]" class="search_l uniform" value="{$sl['id']}" {if $sl['searchable_language'] == 1}checked{/if}>
                                            <span>{$sl['name']}</span>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 after">
                            <div class="">
                                <label class="f_td_group1 f_td_group-pr">
                                    <input type="checkbox" name="sensitive" value="1" {if $app->controller->view->filters->sensitive}checked{/if} title="{$smarty.const.TEXT_SENSITIVE}" class="sens-input uniform"/>
                                    {$smarty.const.TEXT_SENSITIVE}
                                </label>
                            </div>
                            <div class="">
                                <label>
                                    {Html::checkbox('skip_admin', $app->controller->view->filters->skip_admin, ['class' => 'not-admin'])}
                                    {$smarty.const.TEXT_SKIP_ADMIN}
                                </label>
                            </div>
                            <div class="">
                                <label>
                                    {Html::checkbox('untranslated', $app->controller->view->filters->untranslated, ['class' => 'not-translated'])}
                                    {$smarty.const.TEXT_UNTRANSLATED_ONLY}
                                </label>
                            </div>
                            <div class="">
                                <label>
                                    {Html::checkbox('unverified', $app->controller->view->filters->unverified, ['class' => 'not-verified'])}
                                    {$smarty.const.TEXT_UNVERIFIED_ONLY}
                                </label>
                            </div>
                        </div>
                    </div>



				  <div class="filters_buttons">
					<a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
					<button class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
				  </div>
					  <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row|default:null}" />
			  </div>				
			
			</form>
			</div>
		</div>
{if !$empty}		
<form name="save_item_form" id="save_item_form" action="{Url::to(['texts/submit'])}?{http_build_query($get_params)}" method="post">
{/if}
<div class="box-wrap">
{if !$empty}
    <div class="cedit-top redit-top after ">
        <div style="float: left;" class="m-r-4">
            <div class="status-left" >
                <input type="checkbox" name="replace_key" value="on" class="check_bot_switch_on_off" />
                <span>{$smarty.const.TEXT_REPLACE_KEY}</span>
            </div>
        </div>
        <div style="float: left;">
            <div class="status-left filter-box filter-box-pl">
                <input type="checkbox" name="replace_value" value="on" class="check_bot_switch_on_off" />
                <span>{$smarty.const.TEXT_REPLACE_VALUE}</span>
            </div>
        </div>		
    </div>  
{/if}	
    <div class="create-or-wrap after create-cus-wrap w-or-prev-next w-prod-page">
	{if !$empty}
        <div class="widget box box-no-shadow " style="margin-bottom: 0; position: static">
          {if $prev_url neq ''}
          <a href="{$prev_url}" class="btn-next-prev-or btn-prev-or" title="{$smarty.const.TEXT_GO_PREV_ORDER}"></a>
          {else}
          <a href="javascript:void(0)" class="btn-next-prev-or btn-prev-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_PREV_ORDER}"></a>
          {/if}
          {if $next_url neq ''}
          <a href="{$next_url}" class="btn-next-prev-or btn-next-or" title="{$smarty.const.TEXT_GO_NEXT_ORDER}"></a>
          {else}
          <a href="javascript:void(0)" class="btn-next-prev-or btn-next-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_NEXT_ORDER}"></a>
          {/if}
            <div class="widget-header widget-header-review">
                <h4>{$translation_key} (  {$translation_entity} )</h4>
            </div>
            <div class="widget-content">
              {Html::checkbox('trans_status', {$have_untranslated}, ['class' => 'check_on_off'])}
                {foreach $values as $value}
                <div class="wedit-rev after">
                    <label>{$value.flag}<br>{$value.translated_checkbox}</label>
                    <label style="margin-top: 50px;">{$value.checked_checkbox}</label>
                    {if $value.div_class == 'untranslated'}<div class="{$value.div_class}">{$smarty.const.TEXT_UNTRANSLATED}</div>{/if}{Html::textarea($value.name, $value.text, ['class' => {$value.class}, 'rows' => '10'])}
                </div>
                {/foreach}
                {if $selected_entity neq ''}
                  <input name="selected_entity" value="{$selected_entity}" type="hidden">
                {else}
                  <input name="search" value="{$search}" type="hidden">
                {/if}
                {*if $id > 0 *}
                  <!--<input name="id" value="{$id}" type="hidden">-->
                {*/if*}
            </div>
        </div>      
		{/if}
    </div>
</div>
<input type="hidden" name="row" id="row_id" value="{$row}" />
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
    {if !$empty}    
    <div class="btn-right"><button class="btn btn-confirm and-next">{$smarty.const.IMAGE_SAVE_NEXT}</button></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>&nbsp;&nbsp;</div>
    <div class="btn-right"><a href="{Url::to(['texts/edit'])}?{http_build_query($get_params)}&nextut=1" class="btn btn-default">{$smarty.const.IMAGE_NEXT_UNTRANSLATED}</a>&nbsp;&nbsp;</div>
	{/if}
</div>
{if !$empty}		
{Html::input('hidden', 'translation_key', $translation_key)}
{Html::input('hidden', 'translation_entity', $translation_entity)}
{Html::input('hidden', 'sensitive', $sensitive)}
{Html::input('hidden', 'untranslated', $untranslated)}
{Html::input('hidden', 'unverified', $unverified)}
{Html::input('hidden', 'skip_admin', $skip_admin)}
</form>
{/if}

<script>
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
    }
function saveItem() {
    /*$.post("{$app->urlManager->createUrl('texts/submit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#texts_management_data').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");*/

    return true;
}

function backStatement() {
  window.location.href = "{$app->urlManager->createUrl(['texts/'])}?" + ($('input[name=selected_entity]').length>0 && $('input[name=selected_entity]').val().length > 0? 'by=translation_entity&search='+ encodeURIComponent($('input[name=selected_entity]').val()) + '&': ($('input[name=search]').length>0 && $('input[name=search]').val().length>0 ? 'by='+$('select[name=by]').val()+'&search='+encodeURIComponent($('input[name=search]').val())+'&' : '')) + ($('input[name=sensitive]').prop('checked')? 'sensitive=1&': '') + ($('input[name=skip_admin]').prop('checked')? 'skip_admin=1&': '') + ($('input[name=untranslated]').prop('checked')? 'untranslated=1&': '') + ($('input[name=unverified]').prop('checked')? 'unverified=1&': '') + 'row='+localStorage.lastRow;
  return false;
}

$(document).ready(function(){

    const prevNextTop = $('.create-cus-wrap').offset().top - 20;
    const $btnNextPrev = $('.btn-next-prev-or');
    btnNextPrevPosition();
    $(window).on('scroll resize', btnNextPrevPosition)
    function btnNextPrevPosition(){
        let top = $(window).height() / 2 + $(window).scrollTop() - prevNextTop;
        $btnNextPrev.css('top', top)
    }

    localStorage.lastRow = "{$row}";
  
  $('body').on('click', '.and-next', function(){    
    $('#save_item_form').append('<input type="hidden" name="gonext" value="1">');
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
  
  $('textarea.untranslated').keydown(function(){
    $(this).parent().find(':checkbox').prop('checked', true).attr('checked', 'checked').parent().addClass('checked');
  });
  
  $('.btn-xs').click(function(){
	if ($(this).find('i').hasClass('icon-angle-up')){
		localStorage.setItem('filter-opened', false);
	} else {
		localStorage.setItem('filter-opened', true);
	}
  });
  
  
  
  if (!localStorage.hasOwnProperty('filter-opened')){
	localStorage.setItem('filter-opened', false);
  }
  
  if (localStorage.getItem('filter-opened') == 'true'){
	$('.btn-xs').trigger('click');
  }
  

    $(".check_bot_switch_on_off").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    
    $("input[name=trans_status]").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
            onSwitchChange: function(){
              if($(this).is(':checked')){
                $('input[name*=translated]:checkbox').prop('checked', true).parent().addClass('checked');
              }else{
                $('input[name*=translated]:checkbox').prop('checked', false).parent().removeClass('checked');
              }
            }
        }
    );    
});
</script>

</div>
<!-- /Page Content -->




