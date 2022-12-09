<div class="" id="button-{$editor}">
        {if in_array('banner', $buttons)}
                <a href="{$content_widget_url}" class="btn links-popup">{$smarty.const.ADD_BANNER}</a>
        {/if}
        {if in_array('component', $buttons)}
                <a href="{$url}" class="btn links-popup">{$smarty.const.ADD_COMPONENT_KEY}</a>
        {/if}
        {if in_array('component-html', $buttons)}
                <a href="{$url2}" class="btn links-popup">{$smarty.const.ADD_COMPONENT_HTML}</a>
        {/if}
</div>

<script>
    $(function(){
        var button = $('#button-{$editor}');
        var linksPopup = $('.links-popup', button);

        linksPopup.popUp({
                one_popup: false,
                box_class: 'content-widget-popup'
        });
        linksPopup.on('click', function(){
            $('.popup-heading').text($(this).text());
        })
    })
</script>