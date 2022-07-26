<form name="frmUpload" enctype="multipart/form-data" action="{Yii::$app->urlManager->createUrl('texts/export-start')}" method="POST">
    <div>

      <div class="popup-content">
        {foreach $languages as $sl}
            <div>
              <input type="checkbox" name="sl[]" class="search_l" value="{$sl['id']}" id="lng_{$sl['id']}" checked>
              <span><label for="lng_{$sl['id']}">{$sl['name']}</label></span>
            </div>
        {/foreach}
      </div>

      <div class="popup-buttons" style="overflow: hidden;">
        <button type="submit" class="btn btn-primary" >{$smarty.const.IMAGE_CONFIRM}</button>
      </div>
      <input type="hidden" name="params" id="key_params" value="">
      <script>
          $('#key_params').val($('#filterForm').serialize());
      </script>
    </div>
</form>