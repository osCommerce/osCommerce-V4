{use class="Yii"}
{use class="yii\helpers\Html"}
{use class="yii\base\Widget"}
<style>
.sort-box { padding-top: 30px; width:100%; }
.setting-row .sort-box  label{ width:90%!important; }
.setting-row .settings-label{ width:100%; }
.setting-row .settings-label div{ display:inline-block; }
.setting-row .settings-label div:first-child{ width:45%; }
.setting-row .settings-label div+div{ width:25%; }
ul.sortable li div.position, ul.sortable li div.width{ width:25%;display: inline-block;border: 1px solid #ddd; cursor: pointer; padding: 0.5em; height: 40px; vertical-align: top; }
ul.sortable li div.position select{ width:100%; }
ul.sortable li div.name{ width:40%; display: inline-block;  }}
</style>
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_BLOCK}
    </div>
    <div class="popup-content">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_SETTINGS}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>

            </ul>
            <div class="tab-content">

                <div class="tab-pane active" id="type">

                {function drawColumn}
                    <li><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="name">{$content}{if $inEx}<i class="icon-trash"></i>{/if}</div>
                    <div class="position">{Html::dropDownList('setting[0][position_'|cat:$value|cat:']', $settings[0]['position_'|cat:$value], ['left' => 'Left', 'right' => 'Right', 'center' => 'Centre'], ['class' => 'form-control'])}</div>
                    <div class="width">{Html::textInput('setting[0][width_'|cat:$value|cat:']', $settings[0]['width_'|cat:$value], ['class' => 'form-control'])}</div>
                    </li>
                {/function}
                    <div class="clone" style="display:none;">{call drawColumn content=Html::checkbox('setting[0][clone]', false, ['label'=> '', 'class' => 'uniform', 'value' => 'value']) value='value'}</div>
                    <div class="setting-row">
                        <label class="settings-label" for=""><div>{$smarty.const.TEXT_COLUMNS_IN_ROW}</div><div>{$smarty.const.TEXT_POSITION}</div><div>{$smarty.const.TEXT_WIDTH},%</div></label>
                        <div class="sort-box">
                            <ul class="sortable">
                            {if $settings[0].sort_order}
                                {foreach explode(";", $settings[0].sort_order) as $item}
                                    {$ckecked = $settings[0][$item]}
                                    {if isset($attribute->extendedColumns[$item])}
                                        {$inEx = true}
                                    {else}
                                        {$inEx = false}
                                    {/if}
                                    {call drawColumn content=Html::checkbox('setting[0]['|cat:$item|cat:']', $ckecked, ['label'=> $attribute->getLabel($item), 'class' => 'uniform', 'value' => $item]) inEx=$inEx value=$item}
                                {/foreach}
                            {else}
                                {foreach $attribute->baseColumns as $item => $info}
                                    {call drawColumn content=Html::checkbox('setting[0]['|cat:$item|cat:']', false, ['label'=> $attribute->getLabel($item), 'class' => 'uniform', 'value' => $item])}
                                {/foreach}
                            {/if}
                            </ul>
                            <input type="hidden" name="setting[0][sort_order]" value="{$settings[0].sort_order}">
                            <br/>
                            {$smarty.const.TEXT_ADD_MORE}
                            {Html::dropDownList('more_fields', null, $attribute->getMoreCoulmns(), [ 'class' => 'form-control add-element', 'prompt' => 'please select', 'options' => $attribute->getDisabledColumns() ])}
                        </div>
                    </div>

                </div>
                <div class="tab-pane" id="style">
                    {$block_view = 1}
                    {include '../include/style.tpl'}

                </div>
               <script>
                    $(document).ready(function(){
                         function collectFields(){
                              var value = [];
                              $.each($('.uniform:visible'), function(i, e){
                                   value.push($(e).val());
                              })
                              $('input[name="setting[0][sort_order]"]').val(value.join(';'));
                              $('input[name="setting[0][sort_order]"]').trigger('change');
                              $('select[name*="setting[0][position]"]').trigger('change');
                              $(".sortable").disableSelection();
                              $(".uniform:visible").uniform();
                              $('.uniform:visible').trigger('change');
                         }
                         $(".sortable").sortable({
                              stop: function(event, ui){
                                   collectFields();
                              }
                         });
                         
                         collectFields();

                         $('.add-element').change(function(e){
                              var value = e.target.value;
                              if (value.length){
                                   var clone = $('.clone > li').clone();
                                   $("div.name", clone).append('<i class="icon-trash"></i>');
                                   $(clone).find('input:checkbox').val(value);
                                   $(clone).find('input:checkbox').attr("name", 'setting[0]['+value+']');
                                   $(clone).find('div.name label').append($('option[value="'+value+'"]', e.target).text());
                                   $('option[value="'+value+'"]', e.target).attr('disabled', 'disabled');
                                   $(clone).find('select[name="setting[0][position_value]"]').attr('name', "setting[0][position_"+value+"]");
                                   $(clone).find('input[name="setting[0][width_value]"]').attr('name', "setting[0][width_"+value+"]");
                                   $('.sort-box .sortable').append(clone);
                                   $('.sort-box .sortable div.name:last input:checkbox').prop('checked', true);
                                   $('.sort-box .sortable div.name:last input:checkbox').trigger('change');
                                   $('input[name="setting[0][sort_order]"]').trigger('change');
                                   collectFields();
                              }
                         })
                         $('body').on('click', 'i.icon-trash', function(){
                              var value = $(this).parents('div.name').find('input').val();
                              if (value.length){
                                   $('.add-element option[value="'+value+'"]').attr('disabled', false);
                                   $(this).parents('li').remove();
                                   collectFields();
                              }
                         })
                    })
               </script>
            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>