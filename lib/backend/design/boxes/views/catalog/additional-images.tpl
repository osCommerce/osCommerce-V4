{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_BLOCK}
    </div>
    <div class="popup-content">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.HEADING_TYPE}</a></li>
                <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
                <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">

                <div class="tab-pane active" id="type">

                    <div class="setting-row">
                        <label for="">Fancibox</label>
                        <select name="setting[0][fancibox]" id="" class="form-control">
                            <option value=""{if $settings[0].fancibox == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].fancibox == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">Carousel</label>
                        <select name="setting[0][carousel]" id="" class="form-control">
                            <option value=""{if $settings[0].carousel == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].carousel == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>

<div class="carousel-aetings">
    <div class="setting-row">
        <label for="">autoplay</label>
        <select name="setting[0][autoplay]" id="" class="form-control">
            <option value=""{if $settings[0].autoplay == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
            <option value="1"{if $settings[0].autoplay == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
        </select>
    </div>

    <div class="setting-row">
        <label for="">autoplaySpeed</label>
        <input type="text" name="setting[0][autoplaySpeed]" class="form-control" value="{$settings[0].autoplaySpeed}"/>
    </div>
    <div class="setting-row">
        <label for="">speed</label>
        <input type="text" name="setting[0][speed]" class="form-control" value="{$settings[0].speed}"/>
    </div>


    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">

            <li class="active"><a href="#list" data-toggle="tab">Main</a></li>
            {foreach $settings.media_query as $item}
                <li><a href="#list{$item.id}" data-toggle="tab">{$item.setting_value}</a></li>
            {/foreach}

        </ul>
        <div class="tab-content">
            <div class="tab-pane active menu-list" id="list">

                <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
                    <input type="text" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
                </div>

            </div>
            {foreach $settings.media_query as $item}
                <div class="tab-pane menu-list" id="list{$item.id}">

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_COLUMNS_IN_ROW}</label>
                        <input type="text" name="visibility[0][{$item.id}][col_in_row]" class="form-control" value="{$visibility[0][{$item.id}].col_in_row}"/>
                    </div>

                </div>
            {/foreach}

        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        showHide();
        $('select[name="setting[0][carousel]"]').on('change', showHide);
        function showHide() {
            if ($('select[name="setting[0][carousel]"]').val()) {
                $('.carousel-aetings').show()
            } else {
                $('.carousel-aetings').hide()
            }
        }
    })
</script>

                </div>
                <div class="tab-pane" id="style">
                    {$block_view = 1}
                    {include '../include/style.tpl'}

                </div>
                <div class="tab-pane" id="visibility">
                    {include '../include/visibility.tpl'}
                </div>

            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>