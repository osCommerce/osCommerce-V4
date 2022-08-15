{foreach $items as $item}
    <div class="item-holder">
		<div class="item">
                    {if $item.url}
			<div class="item-logo">
				<a target="_blank" href="{$item.url}"><img src="{$item.logo}"></a>
			</div>
                    {/if}
			<h3><a target="_blank" href="{$item.url}"><span class="name">{$item.products_name}</span><span class="descrptShort">{$item.products_description_short}</span></a></h3>
       
			<div class="item-description descrWrap"><span class="descr">{$item.products_description}</span><span class="clickOpen">...</span></div>
			<div class="item-description"><b>Author:</b> {$item.author}</div>
			<div class="item-bottom">
				<div class="item-price">{$item.price}</div>
				<div class="item-actions">
                                {if $item.deployed == 3}
                                    <a class="btn" href="javascript:void(0)" onclick="return file_upload('{$item.products_id}');">{$smarty.const.TEXT_UPDATE}</a>
                                {elseif $item.deployed == 2}  
                                    <a class="btn" href="{$module_list_url}">{$smarty.const.TEXT_INSTALLED}</a>
                                {elseif $item.deployed == 1}
                                    <a class="btn" href="{$module_list_url}">{$smarty.const.TEXT_DOWNLOADED}</a>
                                {elseif !empty($item.filename)}
					<a class="btn" href="javascript:void(0)" onclick="return file_upload('{$item.products_id}');">{$smarty.const.TEXT_INSTALL}</a>
				{else}
					<a class="btn" target="_blank" href="{$item.url}">{$smarty.const.TEXT_DISCOVER}</a>
				{/if}
				</div>
			</div>
		</div>
    </div>
{/foreach}
<div class="app-paginator">
{foreach $pages as $page}
    &nbsp;<a href="javascript:void(0)" onclick="setPage('{($page*$length)}');" class="btn{if $start == ($page*$length)} btn-primary{/if}">{($page+1)}</a>
{/foreach}
</div>
<script>
$('.descr').each(function(){
	if($(this).height()>200){
		$(this).closest('.descrWrap').find('.clickOpen').addClass('showBox');
	}
})
$('.clickOpen').click(function(){

	$(this).closest('.item-description').addClass('open');
})
</script> 
<script type="text/javascript">
  setTimeout(function(){
    $('#store_box').inRow(['.item-logo', '.descrWrap', 'h3'], 3)
  }, 500);
</script>