{foreach $information as $info}
    <option value="{$info['id']}" {*if $info->hide} class="dis_prod"{/if*}>{$info['path']}</option>
{/foreach}