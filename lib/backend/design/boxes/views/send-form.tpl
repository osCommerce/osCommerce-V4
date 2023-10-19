{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
      {$smarty.const.SEND_FORM}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>{$smarty.const.TEXT_TEXT}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">



              <div class="setting-row">
                <label for="email_template">{$smarty.const.TABLE_HEADING_EMAIL_TEMPLATES}</label>
                <select name="setting[0][email_template]" id="email_template" class="form-control">
                  {foreach $templatesList as $template}
                    <option value="{$template}"{if $settings[0].email_template == $template} selected{/if}>{$template}</option>
                  {/foreach}
                </select>
              </div>


                <div class="setting-row">
                    <label for="to_name">{$smarty.const.SEND_TO_NAME}</label>
                    <input type="text"  name="setting[0][to_name]" value="{$settings[0].to_name}" class="form-control" style="width: 243px"/>
                </div>

                <div class="setting-row">
                    <label for="to_email_address">{$smarty.const.SEND_TO_EMAIL}</label>
                    <input type="text"  name="setting[0][to_email_address]" value="{$settings[0].to_email_address}" class="form-control" style="width: 243px"/>
                </div>





                    <ul class="nav nav-tabs">

                          <li class="active" data-bs-toggle="tab" data-bs-target="#form"><a class="form-link">{$smarty.const.TEXT_FORM}</a></li>
                          <li data-bs-toggle="tab" data-bs-target="#success"><a class="success-link">{$smarty.const.SUCCESS_MESSAGE}</a></li>

                    </ul>
                    <div class="tab-content">
                      <div class="tab-pane active" id="form">

                          <textarea name="setting[0][text]" id="htm" style="width: 100%" rows="10">{$text}</textarea>


                          <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.css">
                          <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.css">
                          <script src="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/xml-hint.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/html-hint.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/xml/xml.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/javascript/javascript.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/css/css.js"></script>
                          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>

                          <div id="code"></div>


                          <div class="">
                              {$smarty.const.SEND_FORM_INFO}
                          </div>


                      </div>
                      <div class="tab-pane" id="success">

                          <textarea name="setting[0][success]" id="htm1" style="width: 100%" rows="10">{$success}</textarea>

                          <div id="code1"></div>

                      </div>
                    </div>


            {include 'include/ajax.tpl'}


        </div>
        <div class="tab-pane" id="style">
            {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
            {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
            {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>



  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>

<script type="text/javascript">

    var CodeMirrorEditor;
    var CodeMirrorEditor1;

    $(function(){
        CodeMirrorEditor = CodeMirror(document.getElementById("code"), {
            mode: "text/html",
            extraKeys: { "Ctrl-Space": "autocomplete"},
        });

        var htm = $('#htm');
        CodeMirrorEditor.setValue(htm.val());

        CodeMirrorEditor.on('change', function(){
            htm.val(CodeMirrorEditor.getValue()).trigger('change');
        });

        htm.hide();


        $('#success').show();
        CodeMirrorEditor1 = CodeMirror(document.getElementById("code1"), {
            mode: "text/html",
            extraKeys: { "Ctrl-Space": "autocomplete"},
        });

        var htm1 = $('#htm1');
        CodeMirrorEditor1.setValue(htm1.val());

        CodeMirrorEditor1.on('change', function(){
            htm1.val(CodeMirrorEditor1.getValue()).trigger('change');
        });

        htm1.hide();
        $('#success').css('display', '');
    });


        $('#box-save').on('submit', function(){
            $('#htm').val(CodeMirrorEditor.getValue())
            $('#htm1').val(CodeMirrorEditor1.getValue())
        })
</script>