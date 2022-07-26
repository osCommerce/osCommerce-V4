<div class="order-wrap">
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid"
                       checkable_list="0" data_ajax="image-settings/list">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TABLE_TEXT_NAME}</th>
                        <th>{$smarty.const.TEXT_MAX_WIDTH}</th>
                        <th>{$smarty.const.TEXT_MAX_HEIGHT}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>


    <div class="row right_column" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    var typeId = '';
    $(function(){

        $('.table').on('dblclick', 'tr', function(){
            typeId = $('.type', this).data('type-id');

            window.location = 'image-settings/edit?image_types_id=' + typeId
        });

    });

    function onClickEvent(obj, table){
        var typeId = $('.type', obj).data('type-id');
        $.get("image-settings/bar", {
            'image_types_id' : typeId
        }, function(data){
            $('.right_column .scroll_col').html(data);
        });
    }
</script>