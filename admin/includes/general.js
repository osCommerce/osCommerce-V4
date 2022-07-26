function SetFocus() {
  if (document.forms.length > 0) {
    var field = document.forms[0];
    for (i=0; i<field.length; i++) {
      if ( (field.elements[i].type != "image") &&
           (field.elements[i].type != "hidden") &&
           (field.elements[i].type != "reset") &&
           (field.elements[i].type != "submit") ) {

        document.forms[0].elements[i].focus();

        if ( (field.elements[i].type == "text") ||
             (field.elements[i].type == "password") )
          document.forms[0].elements[i].select();

        break;
      }
    }
  }
}

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}

var editorFieldName = '';
var editorFormName = '';

function loadedHTMLAREA(form,field){
  var height = 768, width = 1024;
  editorFormName = form;
  editorFieldName = field;
  var top = (screen.height) ? (screen.height-height)/2 : 0;
  var left = (screen.width) ? (screen.width-width)/2 : 0;
  window.open('popup_editor.php','editor','status,scrollbars,resizable,width='+width+',height='+height+',top='+top+',left='+left);
} 

function checkbox_addition_image_resize_click(checkbox_checked_status, text_id_image_sm) {
  if (checkbox_checked_status) {
    document.getElementById(text_id_image_sm).disabled = true;
  } else {
    document.getElementById(text_id_image_sm).disabled = false;
  }
}

function ChangeNewImageStyle(checkbox, newImageForm) {
  if(checkbox.checked) {
    document.getElementById(newImageForm).style.display = 'none';
    document.getElementById(newImageForm + '_chooser').style.display = 'none';
  } else {
    document.getElementById(newImageForm).style.display = 'block';
    document.getElementById(newImageForm + '_chooser').style.display = 'block';
  }
}

function cke_preload() {
  if (typeof(CKEDITOR) == 'object'){
    $.each(CKEDITOR.instances, function(i, e){
      if (typeof(e) == 'object'){
        $('textarea[name="'+e.name+'"]').text(e.getData());
      }
    })
  }
}
function ckeplugin(){
if (typeof(CKEDITOR) == 'object'){
    $.each(CKEDITOR.instances, function(i, e){
      if (typeof(e) == 'object'){
        $('#'+e.name).text(e.getData());
      }
    })
  }
}