{use class="yii\helpers\Html"}
<div class="widget box after">
   <style>
     .documents-list{ display: inline-grid;padding: 10px; }
   </style> 
   <div class="widget-header">Assigned Documents</div>
   {Html::beginForm(tep_href_link("printers/save-documents"), 'post', ['id' => 'fDocs'])}
   <div>
    {Html::checkBoxList('documents[]', $assigned, $list, ['class' => 'documents-list'])}
    
   </div>
   <div class="noti-btn">
        <div>
            <span class="btn btn-assign-docs">{$smarty.const.IMAGE_SAVE}</span>
        </div>
   </div>
   {Html::endForm()}
   <script>
    $(document).ready(function(){
        $('.btn-assign-docs').click(function(){
            var post = $('#fDocs').serializeArray();
            post.push({ 'name':'printer_id', value:'{$printer_id}' });
            $.post('{tep_href_link("printers/save-documents")}', post, function(data){
                errors(data);
                $('.pop-up-close:last').trigger('click');
            }, 'json')
        })
    })
   </script>
</div>