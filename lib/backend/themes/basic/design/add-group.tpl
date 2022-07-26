<form class="add-theme-group">
    <div class="popup-heading">{$smarty.const.TEXT_ADD_THEME_GROUP}</div>
    <div class="popup-content add-theme-group">

        <div class="setting-row">
            <label for="">{$smarty.const.TEXT_TITLE_}</label>
            <input type="text" name="title" class="form-control" style="width: 200px">
        </div>

    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
    </div>
</form>

<script>
    $(function(){
        const $addForm = $('.add-theme-group')
        $addForm.on('submit', function(e){
            e.preventDefault();

            const title = $('input[name="title"]', $addForm).val();

            $('.add-theme-group', $addForm).html('<div class="preloader"></div>')
            $('.btn-save', $addForm).remove()

            $.post('design/add-group', { title }, function(data, status){
                if (status != "success") {
                    $('.add-theme-group', $addForm).html('<div class="alert-message">Request error.</div>')
                    return null;
                }
                if (data.error) {
                    $('.add-theme-group', $addForm).html('<div class="alert-message">' + data.error + '</div>')
                }
                if (data.text) {
                    $('.add-theme-group', $addForm).html('<div class="alert-message">' + data.text + '</div>')
                }
                setTimeout(() => document.location.reload(), 300)
            }, 'json')
        })
    })

</script>