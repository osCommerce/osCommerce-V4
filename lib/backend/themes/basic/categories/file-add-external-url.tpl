{use class="yii\helpers\Html"}
<form id="frmSaveDocumentUrl">
<div class="popup-heading">
    {$smarty.const.ADD_DOCUMENT_URL}
</div>
<div class="popup-content box-img doc-gopes-box">

    Group: {Html::dropDownList('new_document_url_type','',$documentTypeVariants,['class'=>'form-control'])}
    Url: {Html::textInput('external_url','',['class'=>'form-control','id'=>'new_document_url'])}
</div>
<div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    <script type="text/javascript">
        $('.btn-cancel').on('click', function(){
            $('.popup-box-wrap').remove()
        });
    </script>

</div>
</form>
<script type="text/javascript">
    $('#frmSaveDocumentUrl').on('submit', function(){
        var name = $('#new_document_url').val();
        var id = $('#global_id').val();
        var obj = 'new_document_url_type';

        var type = $('select[name="'+obj+'"]').val();
        if (type == '') return false;
        var filter = $('#filterForm').find('input').serialize();
        $.post("{Yii::$app->urlManager->createUrl('categories/file-manager-add')}", {
            "name": name,
            "id": id,
            "type": type,
            "filter": filter,
            "isLink": 1
        }, function(data, status){
            if (status == "success") {
                $('#filterForm').html(data);
                var table = $('#document_list').DataTable();
                table.draw(false);
            } else {
                alert("Request error.");
            }
            $('.popup-box-wrap').remove();
        },"html");

        return false;
    });

</script>