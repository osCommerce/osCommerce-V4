{foreach $categories as $info}
    <option value="{$info['id']}" {*if $info->hide} class="dis_prod"{/if*}>{$info['text']}</option>
{/foreach}