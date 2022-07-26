<div class="pageLinksWrapper">
    {if $templates|count > 1}
        <select name="themes" class="form-control" style="margin-bottom: 10px">
        {foreach $templates as $theme_name => $types}
            <option value="{$theme_name}">{$theme_name}</option>
        {/foreach}
        </select>
    {/if}

    {foreach $templates as $theme_name => $types}
        <div class="" data-theme_name="{$theme_name}"{if !$types@first} style="display: none" {/if}>

            {if $types|count > 1}
                <select name="types" class="form-control" style="margin-bottom: 10px">
                    {foreach $types as $type => $names}
                        <option value="{$type}">{$type}</option>
                    {/foreach}
                </select>
            {/if}

            {foreach $types as $type => $names}
                <div class="" data-type="{$type}"{if !$names@first} style="display: none" {/if}>

                    <select name="names" class="component-templates form-control" style="margin-bottom: 10px">
                        {foreach $names as $name => $title}
                            <option value="{$title}">{$title}</option>
                        {/foreach}
                    </select>

                </div>
            {/foreach}
        </div>
    {/foreach}


    <select name="option" class="form-control" style="margin-bottom: 10px">
        <option value="">{$smarty.const.ADD_OPTION}</option>
        <option value="products_id">{$smarty.const.TEXT_PRODUCT}</option>
        <option value="banner">Banner</option>
    </select>

    <div class="option-values" data-option="products_id" style="display: none">
        <input type="hidden" name="products_id" value=""/>
        <input type="text" class="form-control" name="products_name" value="" placeholder="{$smarty.const.TYPE_PRODUCT_NAME_CHOOSE_ROM_LIST}"/>
        <div class="" style="position: relative">
            <div class="suggest" style="position: absolute; top: 0; left: 0"></div>
        </div>
    </div>

    <div class="option-values" data-option="banner" style="display: none">
        <input type="text" name="banner" value="" class="form-control"/>
    </div>

</div>

 <div class="pageLinksButton"><span class="btn btn-primary">{$smarty.const.IMAGE_INSERT}</span></div>


<script type="text/javascript">
    $(function(){
        var wrapper = $('.pageLinksWrapper');

        var selectOption = $('select[name="option"]', wrapper);
        selectOption.on('change', function(){
            $('.option-values').hide();
            $('div[data-option="' + $(this).val() + '"]').show();
            console.log($(this).val());
        });

        var suggest = $('.suggest', wrapper);
        var products_name = $('input[name="products_name"]', wrapper);
        var products_id = $('input[name="products_id"]', wrapper);
        var banner_group = $('input[name="banner_group"]', wrapper);
        products_name.on('keyup', function(e){
            $.get('index/search-suggest', {
                keywords: products_name.val(),
                no_click: true,
                //json: true
            }, function(data){
                suggest.html(data);

                $('a.item', suggest).on('click', function(){
                    products_name.val($('.td_name', this).text())
                    products_id.val($(this).data('id'))
                });

                $('body').on('click', function(){
                    setTimeout(function(){
                        suggest.html('');
                    }, 100)
                })
            })

            if (!$(this).val()) {
                products_id.val('')
            }
        });

        $('select[name="themes"]', wrapper).on('change', function(){
            $('div[data-theme_name]').hide();
            $('div[data-theme_name="' + $(this).val() + '"]').show()
        });

        $('select[name="types"]', wrapper).on('change', function(){
            $('div[data-type]').hide();
            $('div[data-type="' + $(this).val() + '"]').show()
        });

        var oEditor = CKEDITOR.instances.{$smarty.get.editor_id};

        if(oEditor.mode === 'wysiwyg'){
            $('.pageLinksButton span').click(function(){
                var componentTemplates = $('.component-templates:visible');

                {if $isHtml}

                    $.get('design/get-component-html', {
                        'name': componentTemplates.val(),
                        'option': selectOption.val(),
                        'option_val': products_id.val(),
                        'languages_id': {$languages_id},
                        'platform_id': {$platform_id},
                    }, (html) => {

                        if(componentTemplates.val()){
                            oEditor.focus();
                            html = '<div>' + html + '</div>';
                            var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                            oEditor.insertElement( newElement );
                        }
                        $(this).parents('.popup-box-wrap').remove();
                    });

                {else}

                    var option = '';
                    if (selectOption.val() && products_id.val()) {
                        option = '%' + selectOption.val() + '=' + products_id.val()
                    }
                    if (selectOption.val() == 'banner_group' && banner_group.val()) {
                        option = '%' + selectOption.val() + '=' + banner_group.val()
                    }

                    if(componentTemplates.val()){
                        oEditor.focus();
                        var html = '<div>##COMPONENT%' + componentTemplates.val() + option + '##</div>';
                        var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                        oEditor.insertElement( newElement );
                    }
                    $(this).parents('.popup-box-wrap').remove();
                {/if}

            })
        }else{
            $('.pageLinksWrapper').html('{$smarty.const.TEXT_PLEASE_TURN}');
            $('.pageLinksButton').hide();
        }



    })
</script>