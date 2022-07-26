<form class="add-theme-group">
    <input type="hidden" name="theme_name" value="{$theme_name}"/>
    <div class="popup-heading">{$smarty.const.MOVE_TO_GROUP}</div>
    <div class="popup-content add-theme-group">

        <div class="setting-row">
            <label for="">{$smarty.const.TEXT_CHOOSE_GROUP}</label>
            <select name="group_id" class="form-control group_id">
                <option value=""></option>
                <option value="0">{$smarty.const.TEXT_ROOT}</option>
                {foreach $groups as $group}
                    <option value="{$group.themes_group_id}">{$group.title}</option>
                {/foreach}
                <option value="add">{$smarty.const.TEXT_ADD_NEW_GROUP}</option>
            </select>
        </div>

        <div class="setting-row new-group" style="display: none">
            <label for="">{$smarty.const.TEXT_GROUP_NAME}</label>
            <input type="text" name="title" class="form-control" style="width: 243px"/>
        </div>

    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
    </div>
</form>

<script>
    $(function(){
        const $addForm = $('.add-theme-group');

        $('.group_id', $addForm).on('change', function(){
            if ($(this).val() == 'add') {
                $('.new-group').show()
            } else {
                $('.new-group').hide()
            }
        })

        $addForm.on('submit', function(e){
            e.preventDefault();

            const title = $('input[name="title"]', $addForm).val();
            const theme_name = $('input[name="theme_name"]', $addForm).val();
            const group_id = $('.group_id', $addForm).val();

            $('.add-theme-group', $addForm).html('<div class="preloader"></div>')
            $('.btn-save', $addForm).remove()

            $.post('design/theme-move', { title, group_id, theme_name }, function(data, status){
                if (status != "success") {
                    $('.add-theme-group', $addForm).html('<div class="alert-message">Request error.</div>')
                    return null;
                }
                if (data.error) {
                    $('.add-theme-group', $addForm).html('<div class="alert-message">' + data.error + '</div>')
                }
                if (data.text) {
                    $('.add-theme-group', $addForm).html('<div class="alert-message">' + data.text + '</div>')
                    setTimeout(() => document.location.reload(), 300)
                }
            }, 'json')
        })
    })

</script>