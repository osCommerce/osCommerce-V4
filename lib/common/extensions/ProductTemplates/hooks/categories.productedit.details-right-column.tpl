{if \common\helpers\Acl::checkExtensionAllowed('ProductTemplates', 'allowed')}
    {\common\extensions\ProductTemplates\ProductTemplates::productBlock()}
{else}
<div class="widget box box-no-shadow product-frontend-box widget-closed" id="product-template">
    <div class="widget-header">
        <h4>{$smarty.const.CHOOSE_PRODUCT_TEMPLATE}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content widget-content-center dis_module">
        {foreach $app->controller->view->templates.list as $frontend}
            <div class="product-frontend frontend-disable">
                <h4>{$frontend.text}</h4>
                <div>
                    <label>
                        Default
                        <input type="radio" name="product_template[]" value="" class="check_give_wrap" checked disabled>
                    </label>
                </div>
            </div>
        {/foreach}
    </div>
</div>
{/if}
