<div class="" id="buttons-{$editor}">
    {foreach $buttons as $button}
        <a href="{$button.url}" class="btn links-popup {$button.class}">{$button.name}</a>
    {/foreach}
</div>

<script>
    $(function(){
        var buttons = $('#buttons-{$editor}');
        var linksPopup = $('.links-popup', buttons);

        linksPopup.popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_PAGE_LINKS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
            one_popup: false
        });
        linksPopup.on('click', function(){
            $('.popup-heading').text($(this).text());
        })
    })
</script>