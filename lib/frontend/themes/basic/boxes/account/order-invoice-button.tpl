<a class="btn-1" href="{$print_order_link}" target="_blank">{$smarty.const.TEXT_INVOICE}</a>
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