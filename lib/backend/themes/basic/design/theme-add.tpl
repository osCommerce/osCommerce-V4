<form action="" id="add-theme">
    <input type="hidden" name="group_id" value="{$group_id}"/>
    <input type="hidden" name="theme_source" value="empty"/>

    <div class="popup-heading">{$smarty.const.TEXT_ADD_THEME}</div>
    <div class="popup-content pop-mess-cont popup-new-theme">

        <div class="setting-row" style="display: none">
            <label for="">Theme name</label>
            <input type="text" name="theme_name" value="" class="form-control theme_name" style="width: 243px"
                   placeholder="only lowercase letters and numbers"/>

        </div>

        <div class="setting-row">
            <label for="">{$smarty.const.TEXT_THEME_TITLE}</label>
            <input type="text" name="title" value="" class="form-control" style="width: 243px" required/>
        </div>


    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div>
            <button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button>
        </div>
    </div>
</form>
<script type="text/javascript">
    (function ($) {
        $(function () {
            $('.upload[data-name="theme_source_computer"]').uploads();

            $('input[name="theme_source"]').on('change', function () {
                $('.theme-source-content').hide();
                $('.' + $(this).val()).show()
            });

            $('#add-theme').on('submit', function () {
                $('.popup-box').append('<div class="popup-preloader preloader"></div>');
                $.get('{$action}', $('#add-theme').serializeArray(), function (d) {
                    $('.pop-mess-cont .error').remove();
                    if (d.code == 1) {
                        $('.pop-mess-cont').prepend('<div class="error">' + d.text + '</div>');
                        $('.popup-box .popup-preloader').remove();
                    }
                    if (d.code == 2) {
                        $('.pop-mess-cont').prepend('<div class="info">' + d.text + '</div>');

                        location.reload();
                    }
                }, 'json');

                return false
            })
        })
    })(jQuery)
</script>