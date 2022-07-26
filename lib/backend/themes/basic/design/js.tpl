{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{include 'menu.tpl'}


<div class="theme-javascript">
  <textarea name="javascript" id="javascript" cols="30" rows="10">{$javascript}</textarea>
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
  <div id="code2" style="border: 1px solid #ccc"></div>
  <script type="text/javascript">
    var CodeMirrorEditor2;
    $(function(){
      CodeMirrorEditor2 = CodeMirror(document.getElementById("code2"), {
        mode: "text/javascript",
        extraKeys: {
          "Ctrl-Space": "autocomplete",
          "Ctrl-S": function(instance) {
            $.post('design/javascript-save', { theme_name: '{$theme_name}', javascript: instance.getValue()}, function(){ });
            return false;
          }
        },
        //lineNumbers: true,
      });
      var htm = $('#javascript');
      CodeMirrorEditor2.setValue(htm.val());
      htm.hide()
    })
  </script>
  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-right">
      <span data-href="{$link_save}" class="btn btn-save-javascript">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>
</div>


<script type="text/javascript">
  (function(){
    $(function(){
      $('.btn-save-javascript').on('click', function(){
        var javascript = $('#javascript');
        javascript.val(CodeMirrorEditor2.getValue());
        $.post('design/javascript-save', { theme_name: '{$theme_name}', javascript: javascript.val()}, function(){ })
      });

    })
  })(jQuery);
</script>