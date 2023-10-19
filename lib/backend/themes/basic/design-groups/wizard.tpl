{backend\assets\DesignWizardAsset::register($this)|void}
{backend\assets\DesignAsset::register($this)|void}
{backend\assets\SliderAsset::register($this)|void}

<div class="designer-buttons"{if !$themeName} style="display: none" {/if}>
    {include '../design/menu.tpl'}
</div>
<div class="design-wizard" style="padding-top: 40px">

    <div class="wizard-step{if !$themeName} active{else} past{/if}" data-step="name">
        <div class="step-heading">{$smarty.const.TEXT_THEME_NAME}<span class="theme-name">{if $themeTitle}: {$themeTitle}{/if}</span></div>
        <div class="step-content"{if $themeName} style="display: none"{/if}>
            <div class="">
                <input type="text" name="title" placeholder="Enter theme name" class="form-control"/>
                <input type="hidden" name="group_id" value="{$group_id}">
            </div>
            <div class="buttons">
                <span class="btn btn-primary btn-continue{if !count($groupLists)} btn-finish{/if}">{$smarty.const.TEXT_CONTINUE}</span>
            </div>
        </div>
    </div>

    {foreach $groupLists as $step}
        <div class="wizard-step{if $step@index == 0 && $themeName} active{/if}{if $step.multiSelect} multi-select{/if}" data-step="{$step.category}" data-category="{$step.category}">
            <div class="step-heading">{$step.title}</div>
            <div class="step-content">
                <div class="groups-list">
                    {foreach $step.list as $group}
                        <label class="group-item">
                            {if $step.multiSelect}
                                <input type="checkbox" name="{$step.category}{$group.id}" value="{$group.id}"
                                       {if in_array($group.file, $step.files)}checked{/if}/>
                            {else}
                                <input type="radio" name="{$step.category}" value="{$group.id}"
                                       {if in_array($group.file, $step.files)}checked{/if}/>
                            {/if}
                            <div class="group-item-holder">
                                <div class="radio-box"></div>
                                {if $step.multiSelect}
                                    <div class="handle"></div>
                                {/if}
                                <div class="images">
                                    {foreach $group.images as $image}
                                        <div class="image"><img src="{$image.image}"></div>
                                    {/foreach}
                                </div>
                                <div class="">
                                    <div class="title">{$group.name}</div>
                                    <div class="description">{$group.comment}</div>

                                    {if $step.category == 'color' && $group['colors'] && $group['colors']|count}
                                        <div class="main-colors">
                                            <div class="title-colors">{$smarty.const.CHANGE_MAIN_COLORS}:</div>
                                            {foreach $group['colors'] as $color => $names}
                                                    <div class="input-group colorpicker-component">
                                                        <input type="text" name="color" value="{$color}" class="form-control" data-old-color="{$color}" />
                                                        <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
                                                    </div>
                                            {/foreach}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </label>
                    {/foreach}
                </div>
                <div class="buttons">
                    {if $step@last}
                        <span class="btn btn-primary btn-continue btn-finish">{$smarty.const.TEXT_FINISH}</span>
                    {else}
                        <span class="btn btn-primary btn-continue">{$smarty.const.TEXT_CONTINUE}</span>
                    {/if}
                </div>
            </div>
        </div>
    {/foreach}

</div>

<script>
    $(function(){
        $('.wizard-step:not(.active) .step-content').hide();

        $('.colorpicker-component').colorpicker({ sliders: {
                saturation: { maxLeft: 200, maxTop: 200 },
                hue: { maxTop: 200 },
                alpha: { maxTop: 200 }
            }})

        $('.multi-select .groups-list').sortable({
            handle: ".handle",
            axis: "y"
        });

        $('.group-item input:checked').each(function () {
            $(this).closest('.wizard-step').addClass('past');
        });

        $('.btn-continue').on('click', function () {
            const finish = $(this).hasClass('btn-finish');
            const $step = $(this).closest('.wizard-step');
            if ($step.data('step') == 'name') {
                const title = $('input[name="title"]', $step).val();
                if (!title) {
                    return alertMessage('{$smarty.const.ENTER_THEME_NAME}', 'alert-message')
                }
                $('.step-content', $step).addClass('hided-box')
                    .append('<div class="hided-box-holder"><div class="preloader"></div></div>');
                const group_id = $('input[name="group_id"]', $step).val();
                $.post('design-groups/create-theme', { title, group_id }, function (response) {
                    $('.step-content', $step).removeClass('hided-box');
                    $('.hided-box-holder', $step).remove();
                    if (response.error) {
                        alertMessage(response.error, 'alert-message')
                    } else {
                        $.get('design-groups/copy-new-theme')
                        $('.theme-name', $step).html(': ' + title);
                        const url = new URL(window.location.href)
                        url.searchParams.set('theme_name', response.theme_name);
                        window.history.pushState({ },"", url.toString());
                        goToNext(finish && response.theme_name);
                        $('.designer-buttons').slideDown();
                        $('.designer-buttons a').each(function () {
                            const href = $(this).attr('href').replace(/theme_name=$/, 'theme_name='+response.theme_name);
                            $(this).attr('href', href);
                        })
                    }
                }, 'json')
            } else {
                if ($step.data('category')) {
                    let group_id = [];
                    const url = new URL(window.location.href);
                    const theme_name = url.searchParams.get('theme_name');
                    const category = $step.data('category');

                    if ($step.hasClass('multi-select')) {
                        $(`input:checked`, $step).each(function () {
                            group_id.push($(this).val())
                        })
                    } else {
                        group_id = [$(`input[name="${ $step.data('category')}"]:checked`).val()];
                    }

                    $('.step-content', $step).addClass('hided-box')
                        .append('<div class="hided-box-holder"><div class="preloader"></div></div>');
                    let action = 'set-group';
                    if ($step.data('step') == 'color' || $step.data('step') == 'font') {
                        action = 'set-styles';
                    }
                    const colors = [];
                    if ($step.data('step') == 'color') {
                        $('input:checked + .group-item-holder input', $step).each(function () {
                            colors.push({ 'old': $(this).data('old-color'), 'new': $(this).val() })
                        })
                    }
                    $.post('design-groups/' + action, { theme_name, group_id, category, colors }, function (response) {
                        $('.step-content', $step).removeClass('hided-box');
                        $('.hided-box-holder', $step).remove();
                        if (response.error) {
                            console.error(response.error)
                            alertMessage('Errors. See the console for details.', 'alert-message')
                        } else {
                            if (response.widgets && response.widgets.length) {
                                let no = false;
                                let notInstalled = false;
                                const $message = $('<div class="widget-message"></div>')
                                const $no = $(`<div><div class="title">{$smarty.const.EXTENSIONS_YOU_DONT_HAVE}</div></div>`)
                                const $notInstalled = $('<div><div>{$smarty.const.WIDGETS_NOT_INSTALLED_EXTENSIONS}</div></div>')
                                response.widgets.forEach(function (widget) {
                                    if (widget.status == 'no') {
                                        no = true;
                                    }
                                    if (widget.status == 'not-installed') {
                                        notInstalled = true;
                                    }
                                });

                                if (no) {
                                    $message.append($no);
                                }
                                if (notInstalled) {
                                    $message.append($notInstalled);
                                }

                                response.widgets.forEach(function (widget) {
                                    if (widget.status == 'no') {
                                        $no.append(`<div>${ widget.name }</div>`);
                                    }
                                    if (widget.status == 'not-installed') {
                                        $notInstalled.append(`<div>${ widget.name }</div>`);
                                    }
                                });
                                alertMessage($message)
                            }
                            goToNext(finish && theme_name)
                        }
                    }, 'json')
                }
            }
            function goToNext(theme_name) {
                if (theme_name) {
                    window.location = 'design/elements?theme_name=' + theme_name;
                }
                $step.removeClass('active');
                $step.addClass('past');
                $('.step-content', $step).slideUp();
                $step.next().addClass('active').find('.step-content').slideDown();
            }
        });

        $('.step-heading').on('click', function () {
            if ($('.wizard-step[data-step="name"]').hasClass('active')) {
                return null
            }
            const $newStep = $(this).closest('.wizard-step');
            if ($newStep.data('step') == 'name' || $newStep.hasClass('active')) {
                return null
            }
            const $oldStep = $('.wizard-step.active');
            $oldStep.removeClass('active');
            $newStep.addClass('active');
            $('.step-content', $oldStep).slideUp();
            $('.step-content', $newStep).slideDown();
        });

        $('.images').on('click', function(e){
            e.preventDefault();

            const $popUp = $(`
                <div class="mp-wrapper">
                    <div class="mp-shadow"></div>
                    <div class="media-popup">
                        <div class="mp-close"></div>
                        <div class="mp-content"></div>
                     </div>
                 </div>`);

            $('.mp-close', $popUp).on('click', function(){
                $popUp.remove();
            });

            const $popUpContent = $('.mp-content', $popUp);

            const $bigImages = $('<div class="mp-big-images"></div>');
            const $smallImages = $('<div class="mp-small-images"></div>');

            $('.image', this).each(function(){
                $bigImages.append($(this).clone());
                $smallImages.append($(this).clone());
            });

            $popUpContent.append($bigImages);
            $popUpContent.append($smallImages);

            $('body').append($popUp);

            $bigImages.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                //initialSlide: initialSlide,
                asNavFor: '.mp-small-images'
            });
            $smallImages.slick({
                slidesToShow: 9,
                slidesToScroll: 9,
                //initialSlide: initialSlide,
                asNavFor: '.mp-big-images',
                dots: true,
                centerMode: true,
                focusOnSelect: true,
                responsive: [
                    {
                        breakpoint: 1500,
                        settings: {
                            slidesToShow: 7,
                            slidesToScroll: 7
                        }
                    },
                    {
                        breakpoint: 1100,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5
                        }
                    },
                    {
                        breakpoint: 700,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                ]
            })
        })
    })
</script>