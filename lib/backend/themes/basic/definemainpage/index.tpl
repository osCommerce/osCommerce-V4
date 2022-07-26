<FORM ENCTYPE="multipart/form-data" ACTION="{$Yii->baseUrl}/definemainpage/savepage" METHOD=POST  target="upload_target">
    {$this->view->file_contents}

    <p class="btn-toolbar">
        <!--input type="button" class="btn btn-primary" value="Insert" onClick="return savePage()"-->
        <input type="submit" class="btn btn-primary" value="Insert" >
    </p>
    </FORM>

{if ( $this->view->default_editor == 'ckeditor') }
    <script language="JavaScript" type="text/javascript" src="{$Yii->baseUrl}/includes/javascript/ckeditor/ckeditor.js"></script>
{elseif ( $this->view->default_editor  == 'tinimce') }

<script language="JavaScript" type="text/javascript" src="{$Yii->baseUrl}/includes/javascript/tinymce/tinymce.js"></script>
<script language="JavaScript1.2" defer="defer">
    function editorGenerate(){
        tinymce.init({
            mode : "textareas",
            editor_selector : "ckeditor",
            theme: "modern",
            element_format : "html",
            width: 700,
            height: 400,
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor"
            ],
            content_css: "css/content.css",
            toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
            toolbar2: "link image | print preview media fullpage | forecolor backcolor emoticons",
            style_formats: [
                {ldelim}title: 'Bold text', inline: 'b'{rdelim},
                {ldelim}title: 'Red text', inline: 'span', styles: {ldelim}color: '#ff0000'{rdelim}{rdelim},
                {ldelim}title: 'Red header', block: 'h1', styles: {ldelim}color: '#ff0000'{rdelim}{rdelim},
                {ldelim}title: 'Example 1', inline: 'span', classes: 'example1'{rdelim},
                {ldelim}title: 'Example 2', inline: 'span', classes: 'example2'{rdelim},
                {ldelim}title: 'Table styles'{rdelim},
                {ldelim}title: 'Table row 1', selector: 'tr', classes: 'tablerow1'{rdelim}
            ]
        });
    }
</script>
<script language="JavaScript">
    initPage = function (){ldelim}editorGenerate();{rdelim}
    function addEvent(obj, evType, fn) {
         if (obj.addEventListener) {
             obj.addEventListener(evType, fn, true); return true;
         } else if (obj.attachEvent) {
            var r = obj.attachEvent("on"+evType, fn);  return r;  }
        else {
            return false;
        }
    }
    addEvent(window, 'load', initPage);
</script>

{/if}


<iframe id="upload_target" name="upload_target"   style="width:0;height:0;border:0px solid #fff;"></iframe>

<script type="text/javascript">

    function savePage()
    {
        var html = $('textarea#file_contents').val();

        $.post("definemainpage/savepage", {
            'html': html
        }, function (data, status) {
            if (status == "success") {
                //$('#manufacturers_management_data').html(data);
                //$("#manufacturers_management").show();
                //switchOnCollapse('manufacturers_management_collapse');

            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

</script>
