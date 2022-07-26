<form action="{Yii::$app->urlManager->createAbsoluteUrl('subscribers')}" method="get">
  <input type="email" name="subscribers_email_address" required data-pattern="email">
  <button type="button" class="btn">Submit</button>
</form>