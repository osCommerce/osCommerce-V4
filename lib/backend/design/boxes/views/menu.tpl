{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_MENU}
    </div>
    <div class="popup-content">




        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">




                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_CHOSE_MENU} {$params}</label>
                        <select name="params" class="form-control">
                            <option value=""></option>
                            {foreach $menus as $menu}
                                <option value="{$menu.menu_name}"{if $menu.menu_name == $params} selected{/if}>{$menu.menu_name}</option>
                            {/foreach}
                        </select>
                    </div>

                    {if $settings.designer_mode == 'expert'}
                    <div class="setting-row">
                        <label for="">Type</label>
                        <select name="setting[0][type]" class="form-control" data-manager="menu_type">
                            <option value="default">Default</option>
                            <option value="builder"{if $settings[0].type == 'builder'} selected{/if}>Builder</option>
                        </select>
                    </div>
                    {/if}

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIMIT_LEVELS}</label>
                        <input type="text" name="setting[0][limit_levels]" value="{$settings[0].limit_levels}" class="form-control"/>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.SHOW_NEW_CATEGORY_ITEMS}</label>
                        <select name="setting[0][show_new]" class="form-control">
                            <option value=""{if $settings[0].show_new == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].show_new == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.SHOW_COUNT_PRODUCTS}</label>
                        <select name="setting[0][show_count_products]" class="form-control">
                            <option value=""{if $settings[0].show_count_products == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].show_count_products == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>

                    <div class="type-default" data-menu_type="default">

                        <div class="setting-row">
                            <label for="">{$smarty.const.TEXT_MENU_STYLE}</label>
                            <select name="setting[0][class]" class="form-control">
                                <option value=""></option>
                                <option value="menu-style-1"{if $settings[0].class == 'menu-style-1'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL}</option>
                                <option value="menu-slider"{if $settings[0].class == 'menu-slider'} selected{/if}>{$smarty.const.TEXT_MENU_SLIDER}</option>
                                <option value="menu-big"{if $settings[0].class == 'menu-big'} selected{/if}>{$smarty.const.BIG_DROPDOWN_MENU}</option>
                                <option value="menu-style-2"{if $settings[0].class == 'menu-style-2'} selected{/if}>{$smarty.const.TEXT_VERTICAL}</option>
                                <option value="menu-horizontal"{if $settings[0].class == 'menu-horizontal'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL} (Secondary navigation)</option>
                                <option value="menu-simple"{if $settings[0].class == 'menu-simple'} selected{/if}>{$smarty.const.TEXT_VERTICAL} (Secondary navigation)</option>
                                <option value="mobile"{if $settings[0].class == 'mobile'} selected{/if}>mobile</option>
                            </select>
                        </div>

                        <div class="setting-row">
                            <label for="">{$smarty.const.TEXT_SHOW_IMAGES}</label>
                            <select name="setting[0][show_images]" class="form-control">
                                <option value=""{if $settings[0].show_images == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                                <option value="1"{if $settings[0].show_images == '1'} selected{/if}>{$smarty.const.FIRST_LEVEL_IN_ITEM}</option>
                                <option value="10"{if $settings[0].show_images == '10'} selected{/if}>{$smarty.const.FIRST_LEVEL_IN_DROPDOWN}</option>
                            </select>
                        </div>

                        {if $settings.designer_mode}
                        {if $settings.media_query|default:array()|@count > 0}
                            <div style="margin-bottom: 20px">
                                <h4>{$smarty.const.HIDE_MENU_UNDER_ICON}</h4>
                                {foreach $settings.media_query as $item}
                                    <p><label>
                                            <input type="checkbox" name="visibility[0][{$item.id}][hide_menu]"{if $visibility[0][$item.id].hide_menu} checked{/if}/>
                                            {$smarty.const.WINDOW_WIDTH}: {$item.title}
                                        </label></p>
                                {/foreach}
                            </div>
                        {/if}
                        {/if}


                        {include 'include/ajax.tpl'}
                    </div>

                    {if $settings.designer_mode == 'expert'}
                    <div class="type-builder" data-menu_type="builder">

                        <div class="tabbable tabbable-custom box-style-tab">
                            <ul class="nav nav-tabs  style-tabs">

                                <li class="active" data-bs-toggle="tab" data-bs-target="#menu_main"><a>{$smarty.const.TEXT_MAIN}</a></li>
                                <li class="label">{$smarty.const.WINDOW_WIDTH}:</li>
                                {foreach $settings.media_query as $item}
                                    <li data-bs-toggle="tab" data-bs-target="#menu{$item.id}"><a>{$item.title}</a></li>
                                {/foreach}
                            </ul>
                            <div class="tab-content menu-list">

{function builder inp_name='' value='' id=0}

    <div class="setting-row">
        <label for="">{$smarty.const.TEXT_STYLE}</label>
        <select name="{$inp_name}[style]" class="form-control">
            <option value=""></option>
            <option value="1"{if $value.style == '1'} selected{/if}>1</option>
            <option value="2"{if $value.style == '2'} selected{/if}>2</option>
            <option value="3"{if $value.style == '3'} selected{/if}>3</option>
            <option value="4"{if $value.style == '4'} selected{/if}>4</option>
            <option value="5"{if $value.style == '5'} selected{/if}>5</option>
            <option value="6"{if $value.style == '6'} selected{/if}>6</option>
            <option value="7"{if $value.style == '7'} selected{/if}>7</option>
            <option value="8"{if $value.style == '8'} selected{/if}>8</option>
            <option value="9"{if $value.style == '9'} selected{/if}>9</option>
        </select>
    </div>

    <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BURGER_ICON}</label>
        <select name="{$inp_name}[burger_icon]" class="form-control" data-manager="burger_icon_{$id}">
            <option value="">{$smarty.const.TEXT_NO}</option>
            <option value="1"{if $value.burger_icon == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
        </select>
    </div>
    <div class="setting-row" data-burger_icon_{$id}="1">
        <label for="">{$smarty.const.TEXT_OPEN_FROM}</label>
        <select name="{$inp_name}[open_from]" class="form-control" data-manager="open_from_{$id}">
            <option value=""></option>
            <option value="top"{if $value.open_from == 'top'} selected{/if}>{$smarty.const.TEXT_TOP}</option>
            <option value="left"{if $value.open_from == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
        </select>
    </div>
    <div class="setting-row" data-open_from_{$id}="left">
        <label for="">Menu width</label>
        <input type="text" name="{$inp_name}[ofl_width]" value="{$value.ofl_width}" class="form-control"/>
    </div>


    <div class="setting-row">
        <label for="">{$smarty.const.TEXT_POSITION}</label>
        <select name="{$inp_name}[position]" class="form-control"">
            <option value="">{$smarty.const.TEXT_VERTICAL_LEFT}</option>
            <option value="vertical_right"{if $value.position == 'vertical_right'} selected{/if}>{$smarty.const.TEXT_VERTICAL_RIGHT}</option>
            <option value="vertical_center"{if $value.position == 'vertical_center'} selected{/if}>{$smarty.const.TEXT_VERTICAL_CENTER}</option>
            <option value="horizontal_flex"{if $value.position == 'horizontal_flex'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL_FLEX}</option>
            <option value="horizontal_left"{if $value.position == 'horizontal_left'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL_LEFT}</option>
            <option value="horizontal_center"{if $value.position == 'horizontal_center'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL_CENTER}</option>
            <option value="horizontal_right"{if $value.position == 'horizontal_right'} selected{/if}>{$smarty.const.TEXT_HORIZONTAL_RIGHT}</option>
        </select>
    </div>

    <div class="setting-row">
        <label for="">{$smarty.const.TEXT_LIMIT_LEVELS}</label>
        <input type="text" name="{$inp_name}[limit_levels]" value="{$value.limit_levels}" class="form-control"/>
    </div>



    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">

            <li class="active" data-bs-toggle="tab" data-bs-target="#level_1_{$id}"><a>{$smarty.const.TEXT_LEVEL} 1</a></li>
            <li data-bs-toggle="tab" data-bs-target="#level_2_{$id}"><a>{$smarty.const.TEXT_LEVEL} 2</a></li>
            <li data-bs-toggle="tab" data-bs-target="#level_3_{$id}"><a>{$smarty.const.TEXT_LEVEL} 3</a></li>
            <li data-bs-toggle="tab" data-bs-target="#level_4_{$id}"><a>{$smarty.const.TEXT_LEVEL} 4</a></li>
            <li data-bs-toggle="tab" data-bs-target="#level_5_{$id}"><a>{$smarty.const.TEXT_LEVEL} 5</a></li>
            <li data-bs-toggle="tab" data-bs-target="#level_6_{$id}"><a>{$smarty.const.TEXT_LEVEL} 6</a></li>

        </ul>
        <div class="tab-content">
                <div class="tab-pane active menu-list" id="level_1_{$id}">

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIMIT_ITEMS_IN_LEVEL} 1</label>
                        <input type="text" name="{$inp_name}[limit_level_1]" value="{$value.limit_level_1}" class="form-control"/>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_SOW_GO_TO_BUTTON}</label>
                        <select name="{$inp_name}[lev1_goto]" class="form-control">
                            <option value="">{$smarty.const.TEXT_NO}</option>
                            <option value="top"{if $value['lev1_goto'] == 'top'} selected{/if}>{$smarty.const.TEXT_TOP_OF_DROPBOX}</option>
                            <option value="bottom"{if $value['lev1_goto'] == 'bottom'} selected{/if}>{$smarty.const.TEXT_BOTTOM_OF_DROPBOX}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_SHOW_IMAGES}</label>
                        <select name="{$inp_name}[lev1_show_images]" class="form-control">
                            <option value=""{if $value.lev1_show_images == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $value.lev1_show_images == '1'} selected{/if}>{$smarty.const.FIRST_LEVEL_IN_ITEM}</option>
                            <option value="2"{if $value.lev1_show_images == '2'} selected{/if}>{$smarty.const.FIRST_LEVEL_IN_DROPDOWN}</option>
                        </select>
                    </div>

                </div>
            {for $level=2 to 6}
                <div class="tab-pane menu-list" id="level_{$level}_{$id}">

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LEVEL} {$level} {$smarty.const.TEXT_VISIBILITY}</label>
                        <select name="{$inp_name}[lev{$level}_vis]" class="form-control">
                            <option value="">{$smarty.const.TEXT_ALWAYS_VISIBLE}</option>
                            <option value="click"{if $value['lev'|cat:$level|cat:'_vis'] == 'click'} selected{/if}>{$smarty.const.TEXT_SHOW_BY_CLICK}</option>
                            <option value="click_icon"{if $value['lev'|cat:$level|cat:'_vis'] == 'click_icon'} selected{/if}>{$smarty.const.TEXT_SHOW_BY_CLICK_ON_ICON}</option>
                            <option value="over"{if $value['lev'|cat:$level|cat:'_vis'] == 'over'} selected{/if}>{$smarty.const.TEXT_SHOW_BY_MOUSE_OVER}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIMIT_ITEMS_IN_LEVEL} {$level}</label>
                        <input type="text" name="{$inp_name}[limit_level_{$level}]" value="{$value['limit_level_'|cat:$level]}" class="form-control"/>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_SOW_GO_TO_BUTTON}</label>
                        <select name="{$inp_name}[lev{$level}_goto]" class="form-control">
                            <option value="">{$smarty.const.TEXT_NO}</option>
                            <option value="top"{if $value['lev'|cat:$level|cat:'_goto'] == 'top'} selected{/if}>{$smarty.const.TEXT_TOP_OF_DROPBOX}</option>
                            <option value="bottom"{if $value['lev'|cat:$level|cat:'_goto'] == 'bottom'} selected{/if}>{$smarty.const.TEXT_BOTTOM_OF_DROPBOX}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_DISPLAY}</label>
                        <select name="{$inp_name}[lev{$level}_display]" class="form-control">
                            <option value="">{$smarty.const.TEXT_STATIC}</option>
                            <option value="down"{if $value['lev'|cat:$level|cat:'_display'] == 'down'} selected{/if}>
                                {$smarty.const.TEXT_DROP_DOWN}</option>
                            <option value="right"{if $value['lev'|cat:$level|cat:'_display'] == 'right'} selected{/if}>
                                {$smarty.const.TEXT_DROP_RIGHT}</option>
                            <option value="left"{if $value['lev'|cat:$level|cat:'_display'] == 'left'} selected{/if}>
                                {$smarty.const.TEXT_DROP_LEFT}</option>
                            <option value="lra"{if $value['lev'|cat:$level|cat:'_display'] == 'lra'} selected{/if}>
                                {$smarty.const.TEXT_DROP_AUTO_LEFT_RIGHT}</option>
                            <option value="width"{if $value['lev'|cat:$level|cat:'_display'] == 'width'} selected{/if}>
                                {$smarty.const.TEXT_DROP_DOWN_WINDOW_WIDTH}</option>
                            <option value="width_cont"{if $value['lev'|cat:$level|cat:'_display'] == 'width_cont'} selected{/if}>
                                Drop down (container width)</option>
                            <option value="right_top"{if $value['lev'|cat:$level|cat:'_display'] == 'right_top'} selected{/if}>
                                {$smarty.const.TEXT_DROP_RIGHT_START_FROM_TOP}</option>
                            <option value="left_top"{if $value['lev'|cat:$level|cat:'_display'] == 'left_top'} selected{/if}>
                                {$smarty.const.TEXT_DROP_LEFT_START_FROM_TOP}</option>
                            <option value="slide"{if $value['lev'|cat:$level|cat:'_display'] == 'slide'} selected{/if}>Slide over parent</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_SHOW_IMAGES}</label>
                        <select name="{$inp_name}[lev{$level}_show_images]" class="form-control">
                            <option value=""{if $value['lev'|cat:$level|cat:'_show_images'] == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $value['lev'|cat:$level|cat:'_show_images'] == '1'} selected{/if}>{$smarty.const.FIRST_LEVEL_IN_ITEM}</option>
                            <option value="2"{if $value['lev'|cat:$level|cat:'_show_images'] == '2'} selected{/if}>{$smarty.const.FIRST_LEVEL_IN_DROPDOWN}</option>
                        </select>
                    </div>

                </div>
            {/for}
        </div>
    </div>


    <div class="setting-row">
        <label for="">{$smarty.const.TEXT_SHOW_MORE_BUTTON}</label>
        <select name="{$inp_name}[show_more_button]" class="form-control">
            <option value="">{$smarty.const.TEXT_NO}</option>
            <option value="1"{if $value.show_more_button == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
        </select>
    </div>

{/function}

                                <div class="tab-pane active" id="menu_main">

                                    {builder inp_name='setting[0]' value=$settings[0] id=0}

                                </div>
                                {foreach $settings.media_query as $item}
                                    <div class="tab-pane" id="menu{$item.id}">

                                        {*builder name='['|cat:$item.id|cat:']'*}
                                        {builder inp_name='visibility[0]['|cat:$item.id|cat:']'  value=$visibility[0][$item.id] id=$item.id}

                                    </div>
                                {/foreach}
                            </div>
                        </div>

                    </div>
                    {/if}
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
<script>
$(function(){
    $('*[data-manager]').on('change', showManager);
    $('*[data-manager]').each(showManager);

    function showManager(){
        var key = $(this).data('manager');
        var value = $(this).val();
        $('*[data-' + key + ']').each(function(){
            if ($(this).data(key) == value) {
                $(this).show()
            } else {
                $(this).hide()
            }
        })
    }
})
</script>