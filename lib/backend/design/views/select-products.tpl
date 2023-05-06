{\backend\assets\SelectProductsAsset::register($this)|void}

{if !$onlyIncludeJs}
<div {if $name}id="{$name}"{else}class="select-products"{/if}></div>
<script type="text/javascript">
    $(function(){
        $('{if $name}#{$name}{else}.select-products{/if}').selectProducts({
            selectedName: '{$selectedName}',
            selectedProducts: JSON.parse('{$selectedProducts}'),
            selectedPrefix: '{$selectedPrefix}',
            selectedSortName: '{$selectedSortName}',
            selectedBackLink: '{$selectedBackLink}',
            selectedBackLink_c: '{$selectedBackLink_c}',
        })
    })
</script>
{/if}