<div class="select-style" data-type="{$type}">
    {$selected = false}
    <div class="select-style-around"></div>
    <div class="select-style-content">
        <div class="search-style">
            <input type="search" class="form-control" placeholder="{$smarty.const.IMAGE_SEARCH}"/>
        </div>
        <div class="main-styles-list">
            <div data-value="">
                <span class="name">&nbsp;</span>
            </div>
            {foreach $groupStyles as $groupStyle}
                <div class="group" data-name="{$groupStyle.group_name}">
                    <h4 class="group-name" data-target="group-style-{$groupStyle.group_id}">
                        {$groupStyle.group_name}
                    </h4>
                    <div class="group-styles" id="group-style-{$groupStyle.group_id}">
                        {foreach $styles as $mainStyle}
                            {if $mainStyle.group_id == $groupStyle.group_id}
                                <div data-value="${$mainStyle.name}"{if ('$'|cat:$mainStyle.name) == $value} class="selected"{/if}>
                                    {if in_array($mainStyle.type, ['color', 'color-var', 'color-opacity'])}
                                        <span class="style-color" style="background: {$mainSubStyles['$'|cat:$mainStyle.name]}"></span>
                                    {/if}
                                    <span class="name">${$mainStyle.name}</span> - <span class="value">{$mainStyle.value}</span>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            {/foreach}
            {*if array_intersect($type, ['color', 'color-var', 'color-opacity'])}
                <div class="add-color">
                    {$smarty.const.ADD_COLOR}
                </div>
            {/if*}
        </div>
    </div>
    <div class="select-style-selected">
        {if $selected}
            <div>
                {if array_intersect($type, ['color', 'color-var', 'color-opacity'])}
                    <span class="style-color" style="background: {$mainSubStyles['$'|cat:$selected.name]}"></span>
                {/if}
                <span class="name">${$selected.name} </span> - <span class="value">{$selected.value}</span>
            </div>
        {elseif !$selected && $value}
            <div>
                {if array_intersect($type, ['color', 'color-var', 'color-opacity'])}
                    <span class="style-color" style="background: {$mainSubStyles[$value]}"></span>
                {/if}
                <span class="value">{$value}</span>
            </div>
        {/if}
    </div>
    <input type="hidden" name="{$name}" value="{$value}"/>
</div>

<script>
$(function(){
    $('.select-style:not(.applied)').each(function(){
        const $selectStyle = $(this);
        $selectStyle.addClass('applied');
        const type = $(this).data('type');

        $selectStyle.on('click', () => $selectStyle.addClass('opened'));
        $('.select-style-around', $selectStyle).on('click', function () {
            setTimeout(() => $selectStyle.removeClass('opened'), 100);
        });

        $('.search-style input', $selectStyle).on('keyup', function () {
            const keys = $(this).val();
            $('.main-styles-list .name', $selectStyle).each(function () {
                if (!keys || $(this).text().includes(keys)) {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });
            $('.main-styles-list .group', $selectStyle).each(function () {
                if (!keys || $(this).text().includes(keys)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('.main-styles-list div[data-value]', $selectStyle).on('click', selectStyle);
        $('.add-color', $selectStyle).on('click', selectStyle);

        function selectStyle() {
            if ($(this).hasClass('add-color')) {
                const $addStyle = alertMessage(`
                    <div>
                        <div class="popup-heading">{$smarty.const.TEXT_ADD_STYLE}</div>
                        <div class="popup-content">
                            <div class="row align-items-center m-b-2">
                                <div class="col-4 align-right">{$smarty.const.STYLE_NAME}</div>
                                <div class="col-6 align-right"><input type="text" name="name" class="form-control"></div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-4 align-right">{$smarty.const.STYLE_VALUE}</div>
                                <div class="col-6 align-right">
                                    <div class="input-group colorpicker-component">
                                        <input type="text" name="value" class="form-control" />
                                         <span class="input-group-append">
                                            <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                         </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <span class="btn btn-primary btn-add-style">{$smarty.const.TEXT_ADD_STYLE}</span>
                            <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                        </div>
                    </div>
                `);

                const $cp = $('.colorpicker-component', $addStyle)
                $cp.colorpicker({ sliders: {
                        saturation: { maxLeft: 200, maxTop: 200 },
                        hue: { maxTop: 200 },
                        alpha: { maxTop: 200 }
                    }});

                $('input[name="name"]', $addStyle).on('keyup', function () {
                    let val = $(this).val();
                    val = val.replace(/[ ]+/g, '-');
                    val = val.replace('_', '-');
                    val = val.replace(/[^a-zA-Z0-9\-]/g, '');
                    val = val.toLowerCase();
                    $(this).val(val)
                });

                $('.btn-cancel', $addStyle).on('click', () => setTimeout(() => $addStyle.remove(), 0));

                $('.btn-cancel, .pop-up-close, .around-pop-up', $addStyle).on('click', () => $cp.colorpicker('destroy'));

                $('.btn-add-style', $addStyle).on('click', function () {
                    const name = $('input[name="name"]', $addStyle).val();
                    const value = $('input[name="value"]', $addStyle).val();
                    if (!name) {
                        alertMessage('{$smarty.const.ENTER_STYLE_NAME}', 'alert-message');
                        return null
                    }
                    if (!$('input[name="value"]', $addStyle).val()) {
                        alertMessage('{$smarty.const.ENTER_STYLE_VALUE}', 'alert-message');
                        return null
                    }
                    $.post('design/style-add', { theme_name: '{$theme_name}', name, value, type }, function (response) {
                        if (response.error){
                            alertMessage(response.error, 'alert-message')
                        }
                        if (response.text){
                            const $addedMessage = alertMessage(response.text, 'alert-message');
                            //setTimeout(function () {
                                $('.btn-cancel', $addStyle).trigger('click');
                                $addedMessage.remove();
                            //}, 1000);
                            $(` <div data-value="$${ name}">
                                    <span class="style-color" style="background: ${ value}"></span>
                                    <span class="name">$${ name}</span> - <span class="value">${ value}</span>
                                </div>`).on('click', selectStyle).trigger('click').prependTo($('.main-styles-list', $selectStyle));
                        }
                    }, 'json')
                });

            } else {
                $('input[type="hidden"]', $selectStyle).val($(this).data('value')).trigger('change');
                $('.select-style-selected', $selectStyle).html($(this).html());
                $('.main-styles-list .selected', $selectStyle).removeClass('selected');
                setTimeout(() => $selectStyle.removeClass('opened'), 100);
                $(this).addClass('selected')
            }
        }
    })
})
</script>