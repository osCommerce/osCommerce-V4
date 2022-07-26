<form name="frmUpload" enctype="multipart/form-data" action="{Yii::$app->urlManager->createUrl('texts/import-start')}" method="POST">
    <div class="popup-content">
        <p>
        <input name="override" value="1" type="checkbox">{$smarty.const.TEXT_OVERRIDE_KEYS}
        </p>
        <p>
        <input name="addnew" value="1" type="checkbox">{$smarty.const.IMAGE_NEW}
        </p>
        <p>
        <input name="usrfl" type="file" size="50">
        </p>
    </div>
  <div class="popup-buttons" style="overflow: hidden;">
    <button type="submit" class="btn btn-primary" >{$smarty.const.IMAGE_UPLOAD}</button>
  </div>
</form>