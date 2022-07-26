
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--===manufacturers List ===-->
<div class="row">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i>  Manufacturers List  </h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="manufacturers_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0" data_ajax="{$Yii->baseUrl}/manufacturers/list">
                    <thead>
                    <tr>
                        {foreach $this->view->manufacturersTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>
                <p class="btn-toolbar">
                    <input type="button" class="btn btn-primary" value="Insert" onClick="return editManufacturers(0)">
                </p>
            </div>
        </div>
    </div>
</div>
<!-- /manufacturers List -->

<script type="text/javascript">

    function preEditItem(manufacturers_id)
    {
        $.post("manufacturers/itempreedit", {
            'item_id': manufacturers_id
        }, function (data, status) {
            if (status == "success") {
                $('#manufacturers_management_data').html(data);
                $("#manufacturers_management").show();
                switchOnCollapse('manufacturers_management_collapse');

            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editManufacturers(manufacturers_id)
    {
        if(manufacturers_id == 0){
            $("#manufacturer_logo").hide();
            $("#manufacturer_logo").attr("src","");
        }

        switchOnCollapse('manufacturers_management_collapse');
        $('#gallery-filedrop').show();

        $.post("manufacturers/manufactureredit", { 'manufacturers_id' : manufacturers_id }, function(data, status){
            if (status == "success") {
                $('#manufacturers_management_data').html(data);
                $("#manufacturers_management").show();
                switchOffCollapse('manufacturers_list_collapse');
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function saveManufacturer()
    {
        $.post("{$Yii->baseUrl}/manufacturers/manufacturersubmit", $('#save_manufacturer_form').serialize(), function(data, status){
            if (status == "success") {
                $('#manufacturers_management_data').html(data);
                $("#manufacturers_management").show();

                $('.gallery-album-image-placeholder').html('');

                $('.table').DataTable().search( '' ).draw(false);

                setTimeout(function(data){
                    renewLogo();
                }, 3000);

            } else {
                alert("Request error.");
            }
        },"html");

        $('input[name=manufacturers_image_loaded]').val();

        return false;
    }

    function renewLogo()
    {
        var manufacturers_id = $("form[name=save_manufacturer_form] input[name=manufacturers_id]").val();

        $.post("{$Yii->baseUrl}/manufacturers/getlogo", { 'manufacturers_id' : manufacturers_id }, function(data, status){

            if (status == "success") {
                var src = '';
                if(data.image != '') src = data.image;

                $("#image_wrapper").html(' <div class="gallery-template">'+
                '<div class="gallery-media-summary">'+
                '<div class="gallery-album-image-placeholder">'+
                '<img id="manufacturer_logo" src="'+ src +'">'+
                '<span class="elgg-state-uploaded"></span>'+
                '<span class="elgg-state-failed"></span>'+
                ' </div> '+
                ' </div>'+
                ' </div>');

                if(data.is_empty == 'true')
                    $("#manufacturer_logo").hide();

            } else {
                //alert("Request error.");
            }
        },"json");

        return false;
    }

    function deleteManufacturerConfirm(manufacturers_id)
    {
        $.post("manufacturers/confirmmanufacturerdelete", { 'manufacturers_id' : manufacturers_id }, function(data, status){
            if (status == "success") {
                $('#manufacturers_management_data').html(data);
                $("#manufacturers_management").show();
                switchOnCollapse('manufacturers_management_collapse');
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function deleteManufacturer()
    {
        $("#manufacturers_management").hide();
        $.post("manufacturers/manufacturerdelete", $('#manufacturer_delete').serialize(), function(data, status){
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        },"html");

        $("#manufacturer_logo").attr("src","");
        $("#manufacturer_logo").hide();

        return false;
    }

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
        $("#manufacturers_management").hide();

        switchOnCollapse('manufacturers_list_collapse');
        switchOffCollapse('manufacturers_management_collapse');
        var table = $('.table').DataTable();
        table.draw(false);
        $("#manufacturer_logo").attr("src","");
        $("#manufacturer_logo").hide();

        if($('input[name=manufacturers_image_loaded]').val() != ''){
            var image = $('input[name=manufacturers_image_loaded]').val();

            $.post("{$Yii->baseUrl}/manufacturers/dropimage", { 'image' : image }, function(data, status){
                if (status == "success") {

                } else {
                    //alert("Request error.");
                }
            },"html");
        }

        $(window).scrollTop(0);

        $('#gallery-filedrop').hide();

        return false;
    }

    function onClickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();

        $("#manufacturer_logo").attr("src","");
        $("#manufacturer_logo").hide();

        //editManufacturers(event_id);
        preEditItem( event_id );

        $('#gallery-filedrop').hide();
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();
        /*$(table).dataTable({
         destroy: true,
         "ajax": "categories/list/parent/"+event_id
         });*/

        $('#manufacturers_management_data').html('');
        $("#manufacturers_management").hide();
    }

    </script>

<!--===Actions ===-->
<div class="row" id="manufacturers_management" style="display: none;">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i>Manufacturers Management</h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-collapse" id="manufacturers_management_collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div id="gallery-filedrop" class="gallery-filedrop-container">
                <div class="gallery-filedrop">
                    <span class="gallery-filedrop-message">Drag and Drop your files into this area or <a href="#gallery-filedrop" class="gallery-filedrop-fallback-trigger" rel="nofollow">select</a> them from your computer</span>
                    <input size="30" id="gallery-filedrop-fallback" name="gallery_files" class="elgg-input-file hidden" type="file">

                    <div class="gallery-filedrop-queue">
                    </div>

                </div>
                <div class="_hidden" id="image_wrapper">
                    <div class="gallery-template">
                        <div class="gallery-media-summary">
                            <div class="gallery-album-image-placeholder">
                                <img id="manufacturer_logo" src="">
                                <span class="elgg-state-uploaded"></span>
                                <span class="elgg-state-failed"></span>
                            </div>
                            <!--<div class="gallery-filedrop-progressholder">
                                <div class="gallery-filedrop-progress"></div>
                            </div>!-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style" id="manufacturers_management_data">
                Action
            </div>
        </div>
    </div>
</div>
<!--===Actions ===-->

<script type="text/javascript">

    var $filedrop = $('#gallery-filedrop');

    function createImage (file, $container){
        var $preview = $('.gallery-template', $filedrop);
        $image = $('img', $preview);
        var reader = new FileReader();
        $image.width(300);
        reader.onload = function(e){
            $image.attr('src',e.target.result);
        };
        reader.readAsDataURL(file);
        $preview.appendTo($('.gallery-filedrop-queue', $container));
        $.data(file, $preview);
    }

    $(function () {

        $('.gallery-filedrop-fallback-trigger', $filedrop)
                .on('click', function(e) {
                    e.preventDefault();
                    $('#gallery-filedrop-fallback').trigger('click');
                })

        $filedrop.filedrop({
            fallback_id : 'gallery-filedrop-fallback',
            url: '{$Yii->baseUrl}/manufacturers/upload',
            paramname: 'filedrop_files',
            maxFiles: 1,
            maxfilesize : 20,
            allowedfiletypes: ['image/jpeg','image/png','image/gif'],
            allowedfileextensions: ['.jpg','.jpeg','.png','.gif'],
            error: function(err, file) {
                console.log(err);
            },
            uploadStarted: function(i, file, len){
                createImage(file, $filedrop);
            },
            progressUpdated: function(i, file, progress) {
                $.data(file).find('.gallery-filedrop-progress').width(progress);
            },
            uploadFinished: function (i, file, response, time) {
                if (response.status >= 0) {
                    createImage(file, $filedrop);
                    $("#manufacturer_logo").show();
                    $.data(file).find('.elgg-state-uploaded').show();
                    $.data(file).find('.elgg-state-failed').hide();

                    if(response.filename != ''){
                        $('input[name=manufacturers_image_loaded]').val(response.filename);
                    }


                } else {
                    $.data(file).find('.elgg-state-uploaded').hide();
                    $.data(file).find('.elgg-state-failed').show();
                }
            }
        });
    });

</script>