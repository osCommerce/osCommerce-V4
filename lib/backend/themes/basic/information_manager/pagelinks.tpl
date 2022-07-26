<div class="pageLinksWrapper">
    {if $suggest}

        <div class="search">
            <input type="text" value="" placeholder="Enter your keywords" name="keywords" autocomplete="off" class="form-control page-name" onpaste="return false">
            <input type="hidden" value="" class="page-link">
        </div>

    {elseif $select}

    {else}

        <select class="page-link form-control">
            <option value="">None</option>
            {foreach $items as $link => $text}
                <option value="{$link}">{$text}</option>
            {/foreach}
        </select>

    {/if}
</div>

 <div class="pageLinksButton"><span class="btn btn-primary">{$smarty.const.IMAGE_INSERT}</span></div>


<script type="text/javascript">
    function searchSuggestSelected(id, value) {
        $('input.page-name').val(value);
        $('input.page-link').val('catalog/product?products_id='+id);
        return false;
    }
    $(function(){
        var pageLink = $('.page-link');

        {if $suggest}

        var inputSearch = $('.search input');

        inputSearch.on('keyup', function(e){
            jQuery.get('index/search-suggest', {
                keywords: $(this).val()
            }, function(data){
                $('.suggest').remove();
                $('.search').append('<div class="suggest">'+data+'</div>')
            })
        });
        inputSearch.on('blur', function(){
            setTimeout(function(){
                $('.suggest').hide()
            }, 200)
        });
        inputSearch.on('focus', function(){
            $('.suggest').show()
        });

        {/if}


        {if $smarty.get.editor_id}
            var oEditor = CKEDITOR.instances.{$smarty.get.editor_id};
            var getText = function(){
                if (pageLink.prop("tagName").toLowerCase() === 'select'){
                    return $.trim($('option:selected', pageLink).text());
                } else {
                    return $.trim($('.page-name').val());
                }
            };
            var getLink = function(){
                return '##URL##' + pageLink.val();
            };

            if(oEditor.mode === 'wysiwyg'){
                $('.pageLinksButton span').click(function(){
                    if(pageLink.val()){
                        oEditor.focus();
                        if(!oEditor.getSelection().getRanges()[0].collapsed){
                            var fragment = oEditor.getSelection().getRanges()[0].extractContents();
                            var container = CKEDITOR.dom.element.createFromHtml("<a href='"+getLink()+"' />", oEditor.document);
                            fragment.appendTo(container);
                            oEditor.insertElement(container);
                        }else{
                            var html = '<a href="' + getLink() + '">' + getText() + '</a>';
                            var newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                            oEditor.insertElement( newElement );
                        }
                    }
                    $(this).parents('.popup-box-wrap').remove();
                })
            }else{
                $('.pageLinksWrapper').html('{$smarty.const.TEXT_PLEASE_TURN}');
                $('.pageLinksButton').hide();
            }
        {else}
            $('.pageLinksButton span').click(function(){
                if(pageLink.val()){
                    $('input[name="{$smarty.get.field}"]').val(pageLink.val())
                }
                $(this).parents('.popup-box-wrap').remove();
            })
        {/if}
    })
</script>