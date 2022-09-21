<div class="side-action">
    <form>
        <input type="hidden" name="id" value="{$group.id}"/>
        <div class="row">
            <div class="col-md-4"><label>{$smarty.const.TABLE_TEXT_NAME}:</label></div>
            <div class="col-md-8">
                <input type="text" name="name" value="{$group.name}" class="form-control"/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4"><label>{$smarty.const.TEXT_PAGE_TYPE}:</label></div>
            <div class="col-md-8">{$pageTypes}</div>
        </div>
        <div class="row">
            <div class="col-md-4"> </div>
            <div class="col-md-8"><button type="submit" class="btn btn-primary btn-save">Save</button></div>
        </div>
        <div class="row">
            <div class="col-md-4"><label>{$smarty.const.TEXT_DATE_ADDED}</label></div>
            <div class="col-md-8">{\common\helpers\Date::date_format($group.date_added, DATE_FORMAT_SHORT)}</div>
        </div>
        <div class="row first-delete">
            <div class="col-md-4"> </div>
            <div class="col-md-8"><span class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</span></div>
        </div>
        <div class="row confirm-delete" style="display: none">
            <div class="col-md-4"><label>{$smarty.const.IMAGE_CONFIRM}</label></div>
            <div class="col-md-8"><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span> <span class="btn btn-delete-confirm">{$smarty.const.IMAGE_DELETE}</span></div>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('.side-action form').on('submit', function(e){
            e.preventDefault();
            const id = $('.side-action input[name="id"]').val()
            const name = $('.side-action input[name="name"]').val()
            const page_type = $('.side-action select[name="page_type"]').val()

            $.post("design/group-action", { 'action': 'save', id, name, page_type }, function(data, status){
                if (status == "success") {
                    $('.table').DataTable().ajax.reload();
                    alertMessage("{$smarty.const.MESSAGE_SAVED}", 'alert-message');
                    setTimeout(() => $('.popup-box-wrap:last').remove(), 500)
                } else {
                    alertMessage("Request error.", 'alert-message');
                }
            },"html");
        });

        $('.side-action .btn-delete').on('click', function(){
            $('.side-action .first-delete').hide();
            $('.side-action .confirm-delete').show()
        })
        $('.side-action .btn-cancel').on('click', function(){
            $('.side-action .first-delete').show();
            $('.side-action .confirm-delete').hide()
        })
        $('.side-action .btn-delete-confirm').on('click', function(){
            const id = $('.side-action input[name="id"]').val()
            $.post("design/group-action", { 'action': 'delete', id }, function(data, status){
                if (status == "success") {
                    $('.table').DataTable().ajax.reload();
                    alertMessage("{$smarty.const.TEXT_REMOVED}", 'alert-message');
                    setTimeout(() => $('.popup-box-wrap:last').remove(), 500)
                } else {
                    alertMessage("Request error.", 'alert-message');
                }
            },"html");
        })
    })
</script>