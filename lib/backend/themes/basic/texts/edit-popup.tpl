{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<style type="text/css">
    html, body {
        min-width: 0;
    }
    .status-left > span {
        font-size: 13px;
        font-weight: 500;
    }
    .filter-box.filter-box-pl {
        width: 395px;
        padding-left: 2%;
        margin-left: 2%;
        border-left: 1px solid #d9d9d9;
        float: left;
        padding-top: 0;
        margin-top: 0;
        border-top: none;
    }
    .btn-bar {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        background: #fff;
        padding: 20px 0 0;
    }
    .create-or-wrap {
        padding-bottom: 50px;
    }
</style>
<!--=== Page Content ===-->
<div id="texts_management_data">
<!--===Customers List ===-->
         <div class="alert fade in" style="display:none;">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <span id="message_plce"></span>
         </div>
{if !$empty}		
<form name="save_item_form" id="save_item_form" action="{Url::to(['texts/submit'])}?{http_build_query($get_params)}" method="post">
{/if}
<div class="box-wrap">
{if !$empty}
    <div class="cedit-top redit-top after ">
        <div style="float: left;">
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
    <div class="create-or-wrap after create-cus-wrap">
	{if !$empty}
        <div class="widget box box-no-shadow" style="margin-bottom: 0;">
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
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>&nbsp;&nbsp;</div>
	{/if}
</div>
{if !$empty}		
{Html::input('hidden', 'translation_key', $translation_key)}
{Html::input('hidden', 'translation_entity', $translation_entity)}
{Html::input('hidden', 'sensitive', $sensitive)}
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

function backStatement() {
  window.location.href = "{$app->urlManager->createUrl(['texts/'])}?" + ($('input[name=selected_entity]').length>0 && $('input[name=selected_entity]').val().length > 0? 'by=translation_entity&search='+ encodeURIComponent($('input[name=selected_entity]').val()) + '&': ($('input[name=search]').length>0 && $('input[name=search]').val().length>0 ? 'by='+$('select[name=by]').val()+'&search='+encodeURIComponent($('input[name=search]').val())+'&' : '')) + ($('input[name=sensitive]').prop('checked')? 'sensitive=1&': '') + 'row='+localStorage.lastRow;
  return false;
}

$(document).ready(function(){ 

  localStorage.lastRow = "{$row}";

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




