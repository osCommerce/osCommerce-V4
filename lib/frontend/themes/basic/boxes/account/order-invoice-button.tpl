<a class="btn-1 invoice" href="{$print_order_link}" target="_blank">{$smarty.const.TEXT_INVOICE}</a>
{if $showCreditNote}
    <a class="btn-1 credit-note" href="{$linkCreditNote}" target="_blank">{$smarty.const.TEXT_CREDITNOTE}</a>
{/if}
{if $pay_link}
<script>
    tl(function(){
        var box = $('#box-{$id} a.invoice');
        box.addClass('disabled-area');
        box.on('click', function(e){
            e.preventDefault()
        })
    })
</script>
{/if}