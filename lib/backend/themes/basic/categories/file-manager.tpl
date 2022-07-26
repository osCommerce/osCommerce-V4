<div class="popupCategory">
  <div class="file-upload">
  <form id="fileupload" action="#" method="POST" enctype="multipart/form-data">
    Choose file from the list below or <span class="btn btn-upload">Add a new one</span>
    <input name="files" multiple="" type="file" class="file-upload" style="width: 0; height: 0; overflow: hidden">
  </form>
  </div>
    <table class="table" id="document_list">
        <thead>
            <tr>
                <th>{$smarty.const.ICON_FILE}</th>
                <th>{$smarty.const.TABLE_HEADING_ACTION}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {foreach $fileList as $file}
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    <br><br>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
        {*<div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>*}
    </div>
</div>

<script type="text/javascript">
function backStatement() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
    return false;
}

function deleteFile(name) {
    $.post("{Yii::$app->urlManager->createUrl('categories/file-manager-delete')}?name="+name, { }, function(data, status){
        if (status == "success") {
            var table = $('#document_list').DataTable();
            table.draw(false);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function removeFile(name, id) {
  console.log(name);
  $('.docs-item .filename').each(function(){
    if ($(this).text().trim() == name){
      $(this).parents('.docs-item').remove();
      var table = $('#document_list').DataTable();
      table.draw(false);
    }
  });

    /*$.post("{Yii::$app->urlManager->createUrl('categories/file-manager-remove')}", { "name": name, "id": id }, function(data, status){
        if (status == "success") {
            var table = $('#document_list').DataTable();
            table.draw(false);
        } else {
            alert("Request error.");
        }
    },"html");*/
    return false;
}

function addFile(name, id, obj, is_link) {
    var type = $('select[name="'+obj+'"]').val();
    if (type == '') return false;
    var filter = $('#filterForm').find('input').serialize();
    $.post("{Yii::$app->urlManager->createUrl('categories/file-manager-add')}", {
      "name": name,
      "id": id,
      "type": type,
      "filter": filter,
      "isLink": is_link||0
    }, function(data, status){
        if (status == "success") {
          $('#filterForm').html(data);
          var table = $('#document_list').DataTable();
          table.draw(false);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function renameFile(name){
  var cel =$('.file-name[data-name="'+name+'"]');
  cel.html('<input name="file_name" value="'+name+'"> <span class="btn">Save</span>');
  $('.btn', cel).on('click', function(){
    $.get("{Yii::$app->urlManager->createUrl('categories/file-manager-rename')}", {
      "new_name": $('input', cel).val(),
      "name": name
    }, function(d){
      cel.html(d);
      $('input[value="'+name+'"]').val(d);
      $('.filename .filename-txt[data-name="'+name+'"]').html(d)
    })
  })
}

$(document).ready(function(){
  $('.btn-upload').on('click', function(){
    $('.file-upload').trigger('click')
  });

    $('#fileupload').fileupload();
    
    $('#fileupload').fileupload('option', {
        url: '{Yii::$app->urlManager->createUrl('categories/file-manager-upload')}',
        maxFileSize: 2097152,
        maxNumberOfFiles: 100,
        downloadTemplateId: false,
        autoUpload: true,
        acceptFileTypes: /\.+/,
    }).bind('fileuploaddone', function (e, data) {
            var table = $('#document_list').DataTable();
            table.draw(false);
        } );
        
    var options = {
        "searching": true,
        "processing": true,
        "serverSide": true,
      ordering:  false,
        "ajax": {
            "url" : "{Yii::$app->urlManager->createUrl('categories/file-manager-listing')}",
            "dataSrc": function ( json ) {
              if (typeof data_charts != 'undefined' && typeof onDraw == 'function') {
                onDraw(json.data);
              }
              if (typeof json.head == 'object' && typeof onDraw == 'function' ){
              onDraw(json, table);
              }

              if (typeof(rData) == 'object' && rData != null) rData = json;

               return json.data;
            },
            "data" : function ( d ) {
                d.id = $('#global_id').val();
                d.filter = $('#filterForm').find('input').serialize();
                // d.custom = $('#myInput').val();
                // etc
            }
        }
    };
    $('#document_list').dataTable(options);


})
</script>