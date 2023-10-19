{\backend\assets\SelectProductsAsset::register($this)|void}

{if !$onlyIncludeJs}
<div {if $name}id="{$name}"{else}class="select-products"{/if}></div>
<script type="text/javascript">
    $(function(){
      try {
        $('{if $name}#{$name}{else}.select-products{/if}').selectProducts({
            selectTitle: '{$selectTitle}',
            selectedName: '{$selectedName}',
            selectedProducts: JSON.parse('{$selectedProducts}'),
            selectedPrefix: '{$selectedPrefix}',
            selectedSortName: '{$selectedSortName}',
            selectedBackLink: '{$selectedBackLink}',
            selectedBackLink_c: '{$selectedBackLink_c}',
        })
        } catch (e)  {}
    })
</script>
{/if}