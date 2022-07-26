<div class="order-wrap">
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content" id="groups_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid"
                       checkable_list="0" data_ajax="banner_manager/banner-groups-list">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TABLE_TEXT_NAME}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>


    <div class="row right_column" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="groups_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    var groupName = '';
    $(function(){

        $('.table').on('dblclick', 'tr', function(){
            groupName = $('.group', this).data('group-name');

            window.location = 'banner_manager/banner-groups-edit?maps_id=' + mapsId
        });

    });

    function onClickEvent(obj, table){
        var groupName = $('.group', obj).data('group-name');
        $.get("banner_manager/banner-groups-bar", {
            'banners_group' : groupName
        }, function(data){
            $('.right_column .scroll_col').html(data);
        });
    }
</script>