{foreach $responseLog as $item}
    <p>{$item}</p>
{/foreach}
 <a class="btn" href="javascript:void(0)" onclick="return checkActualStatus();">{$smarty.const.IMAGE_BACK}</a>
