{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<fieldset>
        <legend>{$smarty.const.TEXT_INNER_COMMENTS}</legend>
            <div class="f_row">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="24%">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
                            <th>{$smarty.const.TABLE_HEADING_COMMENTS}</th>
                            <th width="25%">{$smarty.const.TABLE_HEADING_PROCESSED_BY}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $comments as $comment}
                            <tr>
                                <td>{\common\helpers\Date::formatDateTime($comment->date_added)}</td>
                                <td>{$comment->comments}</td>
                                <td>{if $comment->admin}{$comment->admin->admin_firstname} {$comment->admin->admin_lastname}{/if}</td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td colspan="3">Nothing added</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>            
                <div class="f_td">
                    <label>{$smarty.const.TEXT_ADD_COMMENT}</label>
                </div>
                <div class="f_td">
                    {Html::textArea('order_comment', '', ['rows' => 5, 'class' => 'form-control order_comment'])}
                </div>
                <div class="f_td">
                    <label>{$smarty.const.TEXT_VISIBLE_TO}</label>
                </div>
                <div class="f_td">
                    <select name="visible_to" class="form-select">
                        <option value="0">All</option>
                        {foreach $groups as $group}
                            {if $group['admins']}
                                <option value="group_{$group['access_levels_id']}" class="group-level">{$group['access_levels_name']}</optgroup></option>
                                {foreach $group['admins'] as $admin}
                                    <option value="member_{$admin['admin_id']}"  class="member-level">&nbsp;&nbsp;&nbsp;{$admin['admin_firstname']} {$admin['admin_lastname']}</option>
                                {/foreach}
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
</fieldset>