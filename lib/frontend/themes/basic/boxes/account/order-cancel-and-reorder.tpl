<a href="{$cancel_and_restart}" class="btn">{$smarty.const.CANCEL_REORDER}</a>
{if $pay_link}
    <script>
        tl(function(){
            var box = $('#box-{$id}');
            box.addClass('disabled-area');
            $('a', box).on('click', function(e){
                e.preventDefault()
            })
        })
    </script>
{/if}