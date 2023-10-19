<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>dsfsdfsdf</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="popupCategory">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            <li class="active" data-bs-toggle="tab" data-bs-target="#tab_2"><a>Name and description</a></li>
            <li data-bs-toggle="tab" data-bs-target="#tab_3"><a>Main details</a></li>
            <li data-bs-toggle="tab" data-bs-target="#tab_4"><a>SEO</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active topTabPane tabbable-custom" id="tab_2">
               <ul class="nav nav-tabs">
                    <li class="active" data-bs-toggle="tab" data-bs-target="#tab_en"><a><img src="{$app->view->theme->baseUrl}/img/en.png"><span>English</span></a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_de"><a><img src="{$app->view->theme->baseUrl}/img/de.png"><span>Germany</span></a></li>
                </ul> 
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_en">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">Name:</td>
                                <td class="label_value"><input type="text" value="Category Name" class="form-control"></td>
                            </tr>
                            <tr>
                                <td class="label_name">Description:</td>
                                <td class="label_value"><textarea class="ckeditor" name="description" id="editor2"></textarea></td>
                            </tr>
                        </table>
                    </div>        
                    <div class="tab-pane" id="tab_de">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">Name:</td>
                                <td class="label_value"><input type="text" value="Category Name" class="form-control"></td>
                            </tr>
                            <tr>
                                <td class="label_name">Description:</td>
                                <td class="label_value"><textarea class="ckeditor" name="description" id="editor3"></textarea></td>
                            </tr>
                        </table>
                    </div>        
                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_3">
                <div class="main_details">
                    <div class="md_row after">
                        <label for="status">Status:</label>
                        <div class="md_value"><input type="checkbox" value="1" name="cat_status" class="check_on_off" checked="checked"></div>
                    </div>
                    <div class="md_row after">
                        <label for="status">Image:</label>
                        <div class="md_value"><span id="upload-file-container"><input type="file" name="cat_img" /></span><span class="cat_upload_img">1111.jpg</span></div>
                    </div>
                    <div class="md_remove">
                        <input type="checkbox" name="cat_img_remove" id="cat_img_remove">
                        <label for="cat_img_remove">Remove</label>
                        <input type="checkbox" name="cat_img_delete" id="cat_img_delete">
                        <label for="cat_img_remove">Delete</label>
                    </div>
                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_4">
                <ul class="nav nav-tabs">
                    <li class="active" data-bs-toggle="tab" data-bs-target="#tab_en"><a><img src="{$app->view->theme->baseUrl}/img/en.png"><span>English</span></a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_de"><a><img src="{$app->view->theme->baseUrl}/img/de.png"><span>Germany</span></a></li>
                </ul> 
                <div class="tab-content seoTab">
                    <div class="tab-pane active" id="tab_en">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">Category SEO Page Name (URL):</td>
                                <td class="label_value"><input type="text" value="Category Name" class="form-control"></td>
                            </tr>
                            <tr>
                                <td class="label_name">Page title:</td>
                                <td class="label_value"><input type="text" value="" class="form-control"></td>
                            </tr>
                            <tr>
                                <td class="label_name">Description meta-tag:</td>
                                <td class="label_value"><textarea rows="5" name="desc_meta_teg"></textarea></td>
                            </tr>
                            <tr>
                                <td class="label_name">Keywords meta-tag:</td>
                                <td class="label_value"><textarea rows="5" name="keywords_meta_teg" placeholder="Add a tag"></textarea></td>
                            </tr>
                            <tr>
                                <td class="label_name">Google Product Category:</td>
                                <td class="label_value"><input type="text" value="" class="form-control"></td>
                            </tr>
                        </table>
                    </div>        
                    <div class="tab-pane" id="tab_de">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">Category SEO Page Name (URL):</td>
                                <td class="label_value"><input type="text" value="Category Name" class="form-control"></td>
                            </tr>
                            <tr>
                                <td class="label_name">Page title:</td>
                                <td class="label_value"><input type="text" value="" class="form-control"></td>
                            </tr>
                            <tr>
                                <td class="label_name">Description meta-tag:</td>
                                <td class="label_value"><textarea rows="5" name="desc_meta_teg"></textarea></td>
                            </tr>
                            <tr>
                                <td class="label_name">Keywords meta-tag:</td>
                                <td class="label_value"><textarea name="keywords_meta_teg" rows="5" placeholder="Add a tag"></textarea></td>
                            </tr>
                            <tr>
                                <td class="label_name">Google Product Category:</td>
                                <td class="label_value"><input type="text" value="" class="form-control"></td>
                            </tr>
                        </table>
                    </div>        
                </div>
            </div>
        </div>
    </div>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">Cancel</a></div>
        <div class="btn-right"><button class="btn btn-primary">Save</button></div>
    </div>
</div>
<script type="text/javascript">
CKEDITOR.replace( 'editor2', {
    toolbar: 'Basic',
    height: 200,
});
CKEDITOR.replace( 'editor3', {
    toolbar: 'Basic',
    height: 200,
});
$(document).ready(function(){
    $(".check_on_off").bootstrapSwitch(
      {
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
      }
    );
})
function backStatement() {
{if $app->controller->view->redirect == 'editcategorypopup'}
    window.history.back();
{else}    
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
{/if}        
    return false;
}
</script>