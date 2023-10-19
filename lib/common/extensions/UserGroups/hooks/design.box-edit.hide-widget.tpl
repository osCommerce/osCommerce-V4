{if $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')}
    {\common\helpers\Translation::init('extensions/user-groups')}
    <div class="col">
        <h4>{$smarty.const.USER_GROUPS}</h4>
        <label class="form-check">
            <input class="form-check-input user-group" type="checkbox" name="user-group-0" value="0">
            {$smarty.const.TEXT_ALL}
        </label>
        {foreach $ext::getGroupsArray() as $group}
            <label class="form-check">
                <input class="form-check-input user-group" type="checkbox" name="user-group-{$group.groups_id}"
                       value="{$group.groups_id}">
                {$group.groups_name}
            </label>
        {/foreach}
    </div>
    <input type="hidden" name="setting[0][user_groups]" value="{$settings[0].user_groups}"
           class="js-user-groups"/>
    <script>
        $(function () {
            const $groupInputs = $('input.user-group:not([value="0"])');
            const $groupInputAll = $('input.user-group[value="0"]');
            const $groups = $('.js-user-groups');
            const groupsStr = $groups.val().trim();
            if (groupsStr) {
                groupsStr.split(',').forEach(function (groupId) {
                    $(`input.user-group[value="${ groupId }"]`).prop('checked', true)
                })
            } else {
                $groupInputs.prop('checked', true)
                $groupInputAll.prop('checked', true)
            }

            $groupInputAll.on('change', function () {
                if ($groupInputAll.prop('checked')) {
                    $groupInputs.prop('checked', true);
                    $groups.val('').trigger('change');
                } else {
                    $groupInputs.prop('checked', false);
                    $groups.val('').trigger('change');
                }
            });
            $groupInputs.on('change', function () {
                let allChecked = true;
                let ids = [];
                $groupInputs.each(function () {
                    if ($(this).prop('checked')) {
                        ids.push($(this).attr('value'))
                    } else {
                        allChecked = false
                    }
                });
                if (allChecked) {
                    $groupInputAll.prop('checked', true);
                    $groups.val('').trigger('change');
                } else {
                    $groupInputAll.prop('checked', false);
                    $groups.val(ids.join(',')).trigger('change');
                }
            });
        })
    </script>
{/if}