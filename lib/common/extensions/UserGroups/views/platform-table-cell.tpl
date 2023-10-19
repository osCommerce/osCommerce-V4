<td>
    {foreach $bannerData.groupsArray as $group}
        <div class="form-check form-switch mt-1">
            <input class="form-check-input user-group"
                   data-platform-id="{$platformId}"
                   data-group-id="{$group.groups_id}"
                   type="checkbox"
                   role="switch"
                   name="user_group[{$platformId}][{$group.groups_id}]"
                    {if is_array($bannerData.platformUserGroups[$platformId]) && in_array($group.groups_id, $bannerData.platformUserGroups[$platformId])} checked{/if}>
            <label class="form-check-label">{$group.groups_name}</label>
        </div>
    {/foreach}
</td>