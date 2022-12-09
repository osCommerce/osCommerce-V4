<div class="widget box box-no-shadow product-frontend-box widget-closed"{if !$app->controller->view->templates.show_block} style="display: none"{/if} id="product-template">
    <div class="widget-header">
        <h4>{$smarty.const.CHOOSE_PRODUCT_TEMPLATE}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content widget-content-center">
        {foreach $app->controller->view->templates.list as $frontend}
            <div class="product-frontend frontend-{$frontend.id}{if !$frontend.active} disable{/if}">
                <h4>{$frontend.text}{if isset($frontend.theme_title)} <span>({$smarty.const.TEXT_THEME_NAME}: {$frontend.theme_title})</span>{/if}
                </h4>
                <div>
                    <label>
                        Default
                        <input type="radio" name="product_template[{$frontend.id}]" value=""
                               class="check_give_wrap"{if !(isset($frontend.template) && $frontend.template)} checked{/if}>
                    </label>
                    {if isset($frontend.templates)}
                    {foreach $frontend.templates as $name}
                        <label>
                            {$name}
                            <input type="radio" name="product_template[{$frontend.id}]" value="{$name}"
                                   class="check_give_wrap"{if isset($frontend.template) && $frontend.template == $name} checked{/if}>
                        </label>
                    {/foreach}
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
</div>