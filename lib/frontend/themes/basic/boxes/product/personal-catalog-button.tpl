{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{Info::addBoxToCss('personal-catalog-popup')}

<div id="{$id}">
    {if $get_button}
        {if $product_in_personal_catalog}
            <span class="btn del_to_personal_catalog">{$smarty.const.DEL_TO_PERSONAL_CATALOG}</span>
        {else}
            <span class="btn add_to_personal_catalog">{$smarty.const.ADD_TO_PERSONAL_CATALOG}</span>
        {/if}
    {else}
        {Html::hiddenInput('personalCatalogButtonWrapId', $id)}
        <div id="personal-button-wrap-{$id}">
            {if $product_in_personal_catalog}
                <span class="btn del_to_personal_catalog">{$smarty.const.DEL_TO_PERSONAL_CATALOG}</span>
            {else}
                <span class="btn add_to_personal_catalog">{$smarty.const.ADD_TO_PERSONAL_CATALOG}</span>
            {/if}
        </div>
        <script type="text/javascript">
            tl([], function () {
                $('div#{$id}').on('click', '.del_to_personal_catalog', function (e) {
                    e.stopPropagation();
                    var dataCatalog = getPostData_{$id}($(this));
                    send_form_{$id}('{Yii::$app->urlManager->createUrl('personal-catalog/confirm-delete')}', dataCatalog)
                });
                $('div#{$id}').on('click', '.add_to_personal_catalog', function (e) {
                    e.stopPropagation();
                    var dataCatalog = getPostData_{$id}($(this));
                    send_form_{$id}('{Yii::$app->urlManager->createUrl('personal-catalog/add')}', dataCatalog);
                });
                function getPostData_{$id}(element) {
                    var product_form = $('#product-form');
                    var dataCatalog = [];
                    if (product_form.length === 0) {
                        product_form = element.closest('div.item');
                        dataCatalog = $('input, select', product_form).serialize();
                        dataCatalog = dataCatalog + '&_csrf=' + $('meta[name="csrf-token"]').attr('content');
                    } else {
                        dataCatalog = product_form.serializeArray();
                    }
                    return dataCatalog;
                }
                function send_form_{$id}(url, dataCatalog) {

                    $.post(url, dataCatalog, function (d) {
                        alertMessage(d.message);
                        if (d.hasOwnProperty('button') && d.button.length > 0) {
                            $('#personal-button-wrap-{$id}').html(d.button);
                        }
                    });
                }
            });
        </script>
    {/if}
</div>
