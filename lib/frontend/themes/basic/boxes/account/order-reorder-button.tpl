{use class="frontend\design\Info"}
<a class="btn btn2" href="{$reorder_link}"{if $reorder_confirm} data-js-confirm="{$reorder_confirm|escape:'html'}"{/if}>{$smarty.const.IMAGE_BUTTON_REORDER}</a>
{if $reorder_confirm}
    <script>
        tl('{Info::themeFile('/js/main.js')}', function(){
            $('#box-{$id} a[data-js-confirm]').on('click', function () {
                alertMessage('<p>'+$(this).attr('data-js-confirm')+'</p><div><a class="btn" href="'+$(this).attr('href')+'">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>');
                return false;
            });
        })
    </script>
{/if}