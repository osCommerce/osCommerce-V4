<form action="" id="add-theme">
    <input type="hidden" name="group_id" value="{$group_id}"/>
    <input type="hidden" name="theme_source" value="theme"/>
    <input type="hidden" name="parent_theme" value="{$theme_name}"/>


    <div class="popup-heading">Copy theme</div>
    <div class="popup-content pop-mess-cont popup-new-theme">

        <div class="setting-row" style="display: none">
            <label for="">New theme name</label>
            <input type="text" name="theme_name" value="" class="form-control theme_name" style="width: 243px"
                   placeholder="only lowercase letters and numbers"/>

        </div>

        <div class="setting-row">
            <label for="" style="width: 200px">Theme title for new theme</label>
            <input type="text" name="title" value="" class="form-control" style="width: 243px" required/>
        </div>

        <div class="setting-row" style="display: none">
            <div class="theme-source-content theme">
                <label><input type="radio" name="parent_theme_files" value="link"/> Link theme
                    files to parent theme</label>
                <label><input type="radio" name="parent_theme_files" value="copy" checked/>
                    Copy theme files</label>
            </div>
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