{use class="frontend\design\Info"}
{if !Info::isAdmin()}
<script>
{if !isset($parents)}
{elseif $parents == 2}
    document.getElementById('box-{$id}')
        .closest('.box-block')
        .style.display = 'none';
{elseif $parents == 3}
    document.getElementById('box-{$id}')
        .closest('.box-block').parentNode.closest('.box-block')
        .style.display = 'none';
{elseif $parents == 4}
    document.getElementById('box-{$id}')
        .closest('.box-block').parentNode.closest('.box-block').parentNode.closest('.box-block')
        .style.display = 'none';
{/if}
</script>
{/if}