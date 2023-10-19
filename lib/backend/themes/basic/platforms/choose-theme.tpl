{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
{\backend\assets\PlatformAsset::register($this)|void}

{$message}

<div class="themes">
    {foreach $themes as $theme}
        <div class="item{if $themeId == $theme.id} selected{/if}" data-id="{$theme.id}">
            <div class="image">
                {if $theme['image']}
                    <img src="{$theme['image']}" alt="">
                {else}
                    <div class="no-image"></div>
                {/if}
            </div>
            <div class="title">{$theme['title']}</div>
            <div class="buttons">
                <button class="btn btn-primary" data-id="{$theme.id}" data-name="{$theme.theme_name}">
                    {$smarty.const.TEXT_ASSIGN}
                </button>
            </div>
            <div class="assigned">
                {$smarty.const.ASSIGNED_THEME}
            </div>
        </div>
    {/foreach}
</div>



<div class="btn-bar">
    <div class="btn-left">
        <button onclick="return window.history.back();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_BACK}</button>
    </div>
    <div class="btn-right"></div>
</div>

<script>
$(function () {
    const platform_id = '{$platformId}';

    $('.themes button').click(function(e){
        e.preventDefault();

        const $popUp = alertMessage('<div class="preloader"></div>');
        const $popUpContent = $('.pop-up-content', $popUp);

        const id = $(this).data('id');
        const theme_name = $(this).data('name');

        let group = '';
        $.get('platforms/theme-banners', { theme_name, platform_id }, function(response){
            if (!response.length) {
                saveItem(id, $popUp);
                return;
            }
            let items = response.reduce(function(sum, current){
                if (!current.banners_group) return sum;
                let item = '';
                if (group != current.banners_group) {
                    group = current.banners_group;
                    item += `<div class="group"><b>${ group}</b></div>`;
                }
                item += `
                    <label class="item">
                        <input type="checkbox" name="banners_id[]" value="${ current.banners_id}"${ (current.theme_banner || current.assigned ? ' checked' : '')}>
                        ${ current.banners_title} ${ (current.assigned ? ' <span class="assigned">({$smarty.const.TEXT_ASSIGNED})</span>' : '')} ${ (current.theme_banner ? ' <span class="theme-banner">({$smarty.const.TEXT_ADDED_WITH_THEME})</span>' : '')}
                    </label>`;

                return sum + item
            }, '');

            const $heading = $(`<div class="popup-heading">{$smarty.const.ASSIGN_BANNERS_TO_PLATFORM}</div>`);
            const $content = $(`<div class="popup-content"><div class="assign-banners">${ items}</div></div>`);
            const $buttons = $(`
                    <div class="popup-buttons">
                        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                        <span class="btn btn-skip">{$smarty.const.SKIP_BANNERS_ASSIGN_THEME}</span>
                        <span class="btn btn-primary">{$smarty.const.TEXT_ASSIGN}</span>
                    </div>`);

            $popUpContent.html('')
                .append($heading)
                .append($content)
                .append($buttons);

            $('.btn-skip', $buttons).on('click', function(){
                saveItem(id, $popUp);
            });

            $('.btn-primary', $buttons).on('click', function(){
                let banners = [];
                $('input', $content).each(function(){
                    banners.push({
                        id: $(this).val(),
                        assigned: $(this).prop('checked') ? 1 : 0
                    })
                });

                $.post('platforms/assign-banners', { banners, platform_id }, function(response){
                    if (response == 'done') {
                        saveItem(id, $popUp);
                    }
                });
            })
        }, 'json');
    });

    function saveItem(theme_id, $popUp) {
        const $popUpContent = $('.pop-up-content', $popUp);
        $.post("{Url::current()}", {
            id: platform_id,
            theme_id
        }, function (data, status) {
            if (status == "success") {
                $popUpContent.html(`<div class="alert-message">${ data.message}</div>`);
                setTimeout(() => $popUp.remove(), 1000);
                $('.themes .selected').removeClass('selected');
                $(`.themes .item[data-id="${ theme_id }"]`).addClass('selected');
            } else {
                alert("Request error.");
            }
        }, 'json');
    }
});
</script>