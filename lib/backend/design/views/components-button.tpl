<div class="" id="button-{$editor}">
        <a href="{$url}" class="btn links-popup">Add component key</a>
        <a href="{$url2}" class="btn links-popup">Add component html</a>
</div>

<script>
    $(function(){
        var button = $('#button-{$editor}');
        var linksPopup = $('.links-popup', button);

        linksPopup.popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_PAGE_LINKS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
            one_popup: false
        });
        linksPopup.on('click', function(){
            $('.popup-heading').text($(this).text());
        })
    })
</script>