{*if !\Yii::$app->user->isGuest*}
<label class="checkbox">
    <input type="checkbox" name="add_to_whishlist" value="{$product.id}"><span></span> {$smarty.const.TEXT_WISHLIST_SAVE}
</label>
{*/if*}