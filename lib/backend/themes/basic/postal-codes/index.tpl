{use class="yii\helpers\Url"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--=== Page Content ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>

                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-ordering table-cities" order_list="0" order_by ="asc" checkable_list="0,1,2" data_ajax="{Url::toRoute('list')}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->columnTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>


                </form>
            </div>

        </div>
    </div>
    <script type="text/javascript">
        function switchOffCollapse(id) {
            if ($("#"+id).children('i').hasClass('icon-angle-down')) {
                $("#"+id).click();
            }
        }

        function switchOnCollapse(id) {
            if ($("#"+id).children('i').hasClass('icon-angle-up')) {
                $("#"+id).click();
            }
        }

        function resetStatement() {
            $("#cities_management").hide();
            switchOnCollapse('cities_list_collapse');
            var table = $('.table').DataTable();
            table.draw(false);
            $(window).scrollTop(0);
            return false;
        }
        function onClickEvent(obj, table) {
            $("#cities_management").hide();
            $('#cities_management_data .scroll_col').html('');
            $('#row_id').val(table.find(obj).index());
            var item_id = $(obj).find('input.cell_identify').val();
            $.post("{Url::toRoute('actions')}", { 'item_id' : item_id }, function(data, status){
                if (status == "success") {
                    $('#cities_management_data .scroll_col').html(data);
                    $("#cities_management").show();
                } else {
                    alert("Request error.");
                }
            },"html");
        }

        function onUnclickEvent(obj, table) {
            $("#cities_management").hide();
            var event_id = $(obj).find('input.cell_identify').val();
            var type_code = $(obj).find('input.cell_type').val();
            $(table).DataTable().draw(false);
        }

        function entryEdit(id){
            $("#cities_management").hide();
            $.get("{Url::toRoute('edit')}", { 'item_id' : id }, function(data, status){
                if (status == "success") {
                    $('#cities_management_data .scroll_col').html(data);
                    $("#cities_management").show();
                    $('#cities_management_data').trigger('dataChanged');
                    switchOffCollapse('cities_list_collapse');
                } else {
                    alert("Request error.");
                }
            },"html");
            return false;
        }

        function entrySave(id){
            $.post("{Url::toRoute('save')}?item_id="+id, $('form[name=cities]').serialize(), function(data, status){
                if (status == "success") {
                    //$('#cities_management_data').html(data);
                    //$("#cities_management").show();
                    $('.alert #message_plce').html('');
                    $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                    resetStatement();
                    switchOffCollapse('cities_list_collapse');
                } else {
                    alert("Request error.");
                }
            },"json");
            return false;
        }

        function entryDelete(id){
            if (confirm('Do you confirm?')){
                $.post("{Url::toRoute('delete')}", { 'item_id' : id}, function(data, status){
                    if (status == "success") {
                        //$('.alert #message_plce').html('');
                        //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                        if (data == 'reset') {
                            resetStatement();
                        } else{
                            $('#cities_management_data .scroll_col').html(data);
                            $("#cities_management").show();
                        }
                        switchOnCollapse('cities_list_collapse');
                    } else {
                        alert("Request error.");
                    }
                },"html");
                return false;
            }
        }

        function update_zone(theForm) {
            var NumState = theForm.zone_id.options.length;
            var SelectedCountry = "";

            while(NumState > 0) {
                NumState--;
                theForm.zone_id.options[NumState] = null;
            }

            SelectedCountry = theForm.country_id.options[theForm.country_id.selectedIndex].value;

            {tep_js_zone_list('SelectedCountry', 'theForm', 'zone_id')}

        }

        $(document).ready(function(){
            $('#cities_management_data').on('dataChanged',function(){
                var $city = $('#cities_management_data input[name="city_name"]');
                $city.autocomplete({
                    source: function(request, response) {
                        $.getJSON('{Url::toRoute('address-city')}', { out_data:['country_id','zone_id'], term : request.term/*, country: $().val()*/ }, response);
                    },
                    minLength: 0,
                    autoFocus: true,
                    appendTo: '#acCityName',
                    delay: 200,
                    open: function (e, ui) {
                        if ($(this).val().length > 0) {
                            var acData = $(this).data('ui-autocomplete');
                            acData.menu.element.find('a').each(function () {
                                var me = $(this);
                                var keywords = acData.term.split(' ').join('|');
                                me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                            });
                        }
                    },
                    response: function( event, ui ) {
                        $('#cities_management_data input[name="city_id"]').val('0');
                        $city.attr('autocomplete', (ui.content.length > 0? 'nope': 'off'));
                    },
                    select: function( event, ui ) {
                        var $country = $('#cities_management_data select[name="country_id"]');
                        var $zone = $('#cities_management_data select[name="zone_id"]');
                        if ( ui.item.country_id && $country.length>0 ){
                            $country.val( ui.item.country_id );
                            setTimeout(function(){
                                $country.trigger('change');
                            },0);
                        }
                        if ( ui.item.zone_id && $zone.length>0 ){
                            setTimeout(function() {
                                $zone.val(ui.item.zone_id);
                                $zone.trigger('change');
                            },10);
                        }

                        var $city_id = $('#cities_management_data input[name="city_id"]');
                        if ( ui.item.city_id &&  $city_id.length>0 ){
                            $city_id.val( ui.item.city_id );
                        }
                        if ( ui.item.city_name && $city.length>0 ){
                            $city.val( ui.item.city_name );
                            $city.trigger('change');
                        }
                        // setTimeout(function(){
                        //     $city.trigger('change');
                        // }, 200)
                    }
                }).focus(function () {
                    $city.autocomplete("search");
                }).autocomplete( 'instance' )._renderItem = function( ul, item ) {
                    var extend_info = [];
                    if (item.zone_name) extend_info.push(item.zone_name);
                    if (item.country_name) extend_info.push(item.country_name);
                    return $( "<li>" )
                        .append( "<div>" + item.label + "<br><span class='post-code-complete-address'>" + extend_info.join(', ') + "</div>" )
                        .appendTo( ul );
                };

            });
        });

    </script>
    <!--===Actions ===-->
    <div class="row right_column" id="cities_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="cities_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->
</div>