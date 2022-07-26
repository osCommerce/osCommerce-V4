{use class = "yii\helpers\Html"}
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
    function documentSearchSelected(name, link) {
        $('input.page-name').val(name);
        $('input.page-link').val(link);
        return false;
    }
    $(function(){

        var oEditor = CKEDITOR.instances.{$editorId};
        var pageLink = $('.page-link');

        {if $suggest}

        var inputSearch = $('.search input');

        inputSearch.on('keyup', function(e){
            jQuery.get("{$app->urlManager->createUrl(['categories/file-filter'])}", {
                keywords: $(this).val(),
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


        var getText = function(){
            if (pageLink.prop("tagName").toLowerCase() === 'select'){
                return $.trim($('option:selected', pageLink).text());
            } else {
                return $.trim($('.page-name').val());
            }
        };
        var buildLink = function(){
            var link = pageLink.val();
            if (link.indexOf('http') === 0) {
                return pageLink.val();
            }
            return '{$host}' + pageLink.val();
        };

        if(oEditor.mode === 'wysiwyg'){
            $('.pageLinksButton span').click(function(){
                if(pageLink.val()){
                    oEditor.focus();
                    if(!oEditor.getSelection().getRanges()[0].collapsed){
                        var fragment = oEditor.getSelection().getRanges()[0].extractContents();
                        var container = CKEDITOR.dom.element.createFromHtml('<a target="_blank" href="'+buildLink()+'" />', oEditor.document);
                        fragment.appendTo(container);
                        oEditor.insertElement(container);
                    }else{
                        var html = '<a target="_blank" href="' + buildLink() + '">' + getText() + '</a>';
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
    });
</script>
