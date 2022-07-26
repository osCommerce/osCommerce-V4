{foreach $information as $info}
    <option value="{$info->information_id}" {if $info->hide} class="dis_prod"{/if}>{$info->page_title}</option>
{/foreach}