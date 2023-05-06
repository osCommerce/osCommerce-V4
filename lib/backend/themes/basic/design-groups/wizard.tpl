{backend\assets\DesignWizardAsset::register($this)|void}
{backend\assets\DesignAsset::register($this)|void}
{backend\assets\SliderAsset::register($this)|void}

{if $themeName}
    {include '../design/menu.tpl'}
{/if}
<div class="design-wizard"{if $themeName} style="padding-top: 40px" {/if}>

    <div class="wizard-step{if !$themeName} active{else} past{/if}" data-step="name">
        <div class="step-heading">{$smarty.const.TEXT_THEME_NAME}<span class="theme-name">{if $themeTitle}: {$themeTitle}{/if}</span></div>
        <div class="step-content"{if $themeName} style="display: none"{/if}>
            <div class="">
                <input type="text" name="title" placeholder="Enter theme name" class="form-control"/>
                <input type="hidden" name="group_id" value="{$group_id}">
            </div>
            <div class="buttons">
                <span class="btn btn-primary btn-continue">{$smarty.const.TEXT_CONTINUE}</span>
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
                                <input type="checkbox" name="{$step.category}{$group.id}" value="{$group.id}"/>
                            {else}
                                <input type="radio" name="{$step.category}" value="{$group.id}"/>
                            {/if}
                            <div class="group-item-holder">
                                <div class="radio-box"></div>
                                <div class="images">
                                    {foreach $group.images as $image}
                                        <div class="image"><img src="{$image.image}"></div>
                                    {/foreach}
                                </div>
                                <div class="">
                                    <div class="title">{$group.name}</div>
                                    <div class="description">{$group.comment}</div>
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
                        $('.theme-name', $step).html(': ' + title);
                        const url = new URL(window.location.href)
                        url.searchParams.set('theme_name', response.theme_name);
                        window.history.pushState({ },"", url.toString())
                        goToNext()
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
                    $.post('design-groups/' + action, { theme_name, group_id, category }, function (response) {
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
                            goToNext(finish)
                        }
                    }, 'json')
                }
            }
            function goToNext(finish) {
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