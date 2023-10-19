{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_LISTING_SETTINGS}
  </div>
  <div class="popup-content box-img">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_LISTING_SETTINGS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="setting-row">
            <label for="">{$smarty.const.SHOW_COMPARE_BUTTON}</label>
            <select name="setting[0][compare_button]" id="" class="form-control">
              <option value=""{if $settings[0].compare_button == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="1"{if $settings[0].compare_button == '1'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>


          <div class="setting-row">
            <label for="">Sorting</label>

            <div class="drop-list list-sorting" style="clear: both; padding: 0; border: none">
              <ul>
                {foreach $sorting as $key => $item}
                  <li>
                    <div class="item-handle"><div class="item-handle-move"></div>
                      {$item.title}
                      <div class="link-setting-c">
                        <input type="checkbox" name="setting[0][sort_hide_{$item.name}]" class="check_on_off"{if $item.hide} checked{/if}/>
                      </div>
                      <input type="hidden" name="setting[0][sort_pos_{$item.name}]" value="{$key}"/>
                    </div>
                  </li>
                {/foreach}
              </ul>
            </div>
          </div>
          <script type="text/javascript">
            (function($){
              $(function(){

                $( ".list-sorting > ul" ).sortable({
                  handle: ".item-handle-move",
                  items: "> li",
                  stop: function(e, ui){
                    $('.list-sorting .item-handle input[type="hidden"]').each(function(i){
                      $(this).val(i+1)
                    })
                  }
                });

                $(".check_on_off").bootstrapSwitch({
                  onText: "{$smarty.const.SW_ON}",
                  offText: "{$smarty.const.SW_OFF}",
                  handleWidth: '20px',
                  labelWidth: '24px'
                });

              })
            })(jQuery)
          </script>


        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>