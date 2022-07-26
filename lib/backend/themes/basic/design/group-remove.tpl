<form class="remove-theme-group">
    <input type="hidden" name="group_id" value="{$group_id}"/>

    <div class="popup-heading">{$smarty.const.TEXT_REMOVE_GROUP}: "{$groupTitle}"</div>
    <div class="popup-content">

        {if count($themes) > 0}
            <div class="">{$smarty.const.NEXT_THEMES_WILL_BE_REMOVED}:</div>
            {foreach $themes as $theme}
                <div class="">{$theme.title}</div>
            {/foreach}
        {else}
            <div class="">{$smarty.const.TEXT_GROUP_IS_EMPTY}</div>
        {/if}

    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_REMOVE}</button></div>
    </div>
</form>

<script>
    $(function(){
        const $addForm = $('.remove-theme-group')
        $addForm.on('submit', function(e){
            e.preventDefault();

            const group_id = $('input[name="group_id"]', $addForm).val();

            $('.popup-content', $addForm).html('<div class="preloader"></div>')
            $('.btn-save', $addForm).remove()

            $.post('design/group-remove', { group_id }, function(data, status){
                if (status != "success") {
                    $('.popup-content', $addForm).html('<div class="alert-message">Request error.</div>')
                    return null;
                }
                if (data.error) {
                    $('.popup-content', $addForm).html('<div class="alert-message">' + data.error + '</div>')
                }
                if (data.text) {
                    $('.popup-content', $addForm).html('<div class="alert-message">' + data.text + '</div>')
                }
                setTimeout(() => document.location.reload(), 300)
            }, 'json')
        })
    })

</script>