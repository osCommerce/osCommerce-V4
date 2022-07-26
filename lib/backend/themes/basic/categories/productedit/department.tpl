{use class="\common\classes\department"}
{use class="yii\helpers\Html"}
<div class="filter_pad">
  <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
    <thead>
    <tr>
      <th>{$smarty.const.TABLE_HEAD_DEPARTMENT_NAME}</th>
      <th>{$smarty.const.TABLE_HEAD_DEPARTMENT_PRODUCT_ASSIGN}</th>
    </tr>
    </thead>
    <tbody>
    {foreach department::getCatalogAssignList() as $department}
      <tr>
        <td>{$department['text']}{if $department['id'] eq $pInfo->created_by_department_id} {$smarty.const.OWNER_DEPARTMENT}{/if}</td>
        <td>
          {Html::checkbox('departments[]', isset($app->controller->view->department_assigned[$department['id']]), ['value' => $department['id'],'class'=>'check_on_off_department'])}
          {Html::hiddenInput('department_activate_parent_categories['|cat:$department['id']|cat:']','',['class'=>'js-department_parent_categories'])}
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <input type="hidden" name="department_assign_present" value="1">
</div>
<script type="text/javascript">
    $(function() {
        var activate_department_categories = {$json_department_activate_categories};
        $('.check_on_off_department').bootstrapSwitch( {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            onSwitchChange: function (ob, st) {
                var switched_to_state = false;
                if($(this).is(':checked')){
                    switched_to_state = true;
                }
                $(window).trigger('department_changed', [ob, st]);

                if (switched_to_state && this.name.indexOf('departments')==0 ) {
                    var department_id = this.value;
                    var askActivateCategories = '';
                    if ( activate_department_categories[department_id] ) {
                        for( var cat_id in activate_department_categories[department_id]){
                            if ( !activate_department_categories[department_id].hasOwnProperty(cat_id) ) continue;
                            askActivateCategories += '<br><label><input name="_assign_select[]" class="js-department_activate_parent_categories_select" '+(activate_department_categories[department_id][cat_id]['selected']?' checked="checked" disabled="disabled" readonly="readonly"':'')+' type="checkbox" value="'+cat_id+'"> '+activate_department_categories[department_id][cat_id]['label']+'</label>';
                        }
                    }

                    var $state_input = $('.js-department_parent_categories').filter('input[name="department_activate_parent_categories['+department_id+']"]');
                    if ( switched_to_state && $state_input.val()=='' && (askActivateCategories.length>0) ) {
                        $('body').append(
                            '<div class="popup-box-wrap confirm-popup js-state-confirm-popup">' +
                            '<div class="around-pop-up"></div>' +
                            '<div class="popup-box"><div class="pop-up-close"></div>' +
                            '<div class="pop-up-content">' +
                            '<div class="confirm-text">{$smarty.const.TEXT_ASK_ENABLE_DEPARTMENT_PRODUCT_CATEGORIES} '+askActivateCategories+'</div>' +
                            '<div class="buttons"><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_NO}</span><span class="btn btn-default btn-success">{$smarty.const.TEXT_BTN_YES}</span></div>' +
                            '</div>' +
                            '</div>' +
                            '</div>');
                        $('.popup-box-wrap').css('top', $(window).scrollTop() + Math.max(($(window).height() - $('.popup-box').height()) / 2,0));
                        if ( $('.js-department_activate_parent_categories_select').filter(':checked').length==0 ) {
                            $('.js-department_activate_parent_categories_select').trigger('click');
                        }

                        var $popup = $('.js-state-confirm-popup');
                        $popup.find('.pop-up-close').on('click', function(){
                            $('.popup-box-wrap:last').remove();
                        });
                        $popup.find('.btn-cancel').on('click', function(){
                            $state_input.val('');
                            $('.popup-box-wrap:last').remove();
                        });
                        $popup.find('.btn-success').on('click', function(){
                            var selected_values = [];
                            $('.js-department_activate_parent_categories_select:checked').each(function(){
                                selected_values.push(this.value);
                            });
                            $state_input.val(selected_values.join(','));
                            $('.popup-box-wrap:last').remove();
                        });
                    }

                }
            },
            handleWidth: '20px',
            labelWidth: '24px'
        } );
    } );
</script>