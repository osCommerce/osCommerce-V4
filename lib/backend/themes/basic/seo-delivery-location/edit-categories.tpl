<div class="xl-pr-box">
    <div class="after">
        <div class="attr-box attr-box-1">
            <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIND_PRODUCT_CATEGORIES}</h4>
                    <div class="box-head-serch after">
                        <input type="search" id="searchCategory" placeholder="{$smarty.const.SEARCH_BY_INFORMATION}" class="form-control">
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content">
                    <select id="selectSearchCategories" size="25" style="width: 100%; height: 100%; border: none;" ondblclick="addSelectedCategory()" multiple="multiple">
                    </select>
                </div>
            </div>
        </div>
        <div class="attr-box attr-box-2">
            <span class="btn btn-primary" onclick="addSelectedCategory()"></span>
        </div>
        <div class="attr-box attr-box-3">
            <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.ASSIGNED_PRODUCT_CATEGORIES}</h4>
                </div>
                <div class="widget-content">
                    <table class="table assig-attr-sub-table set-assigned-categories">
                        <thead>
                        <tr role="row">
                            <th>{$smarty.const.TABLE_TEXT_NAME}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        {foreach $addedCategories as $categoru}
                            <tr>
                                <td class="ast-name-element">
                                    {$categoru.text}
                                    <input type="hidden" name="selectedCategories[]" value="{$categoru.id}">
                                </td>
                                <td class="remove-ast" onclick="deleteSelectedCategory(this)"></td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function addSelectedCategory() {
        $('#selectSearchCategories option:selected').each(function() {
            var information_id = $(this).val();
            var text = $(this).text();
            console.log(information_id);
            if ( $('.set-assigned-categories input[value="' + information_id + '"]').length === 0 ) {
                $( ".set-assigned-categories tbody" ).append(`
                <tr>
                    <td class="ast-name-element">${ text}<input type="hidden" name="selectedCategories[]" value="${ information_id}"></td>
                    <td class="remove-ast" onclick="deleteSelectedCategory(this)"></td>
                </tr>
                `);
            }
        });

        return false;
    }

    function deleteSelectedCategory(obj) {
        $(obj).parent().remove();
        return false;
    }

    $(document).ready(function() {
        var infoPageList = $( "select#selectSearchCategories" );

        $('#searchCategory').on('focus keyup', function(e) {
            var str = $(this).val();
            $.post( "{Yii::$app->urlManager->createUrl('seo-delivery-location/get-categories')}"
                ,{
                    'platform_id':{$platform_id},
                    'term':encodeURIComponent(str),
                }, function( data ) {
                    infoPageList.html( data );
                });
        }).keyup();
    });
</script>
