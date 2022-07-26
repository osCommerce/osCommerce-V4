import searchProduct from "src/search-product";
import searchCategory from "src/search-category";
import searchInfo from "src/search-info-page";
$.fn.searchProduct = searchProduct;
$.fn.searchCategory = searchCategory;
$.fn.searchInfo = searchInfo;

$.fn.cloneRow = function(){
    return this.each(function(){
        $(this).addClass('cloned').append('<span class="remove"></span>');

        $('input, select, textarea', this).each(function(){
            this.addEventListener('keydown', function(e) { e.stopPropagation(); }, false);
            $(this).val('')
        });

        $('.remove', this).on('click', () => $(this).remove())
    })
};

let attributesPopup = function(object, save, unload){

    let popupTemplateHolder = $('#svg-item-attributes');

    let popupTemplate = popupTemplateHolder.html();

    let popupContent = function(){

        popupTemplateHolder.html('');

        let form = $('.popup-box form');
        let popUpInputs = $('input, select, textarea', form);

        popUpInputs.each(function(){
            let name = $(this).attr('name');
            let val  = object._attributes[$(this).attr('name')];
            if (typeof val === "object") {
                let repetitiveBox = $('.r-'+name.replace(/\[[0-9]+\]/, ''));
                if (repetitiveBox.length !== val.length){
                    let needAdd = val.length - repetitiveBox.length;
                    for(let i = 0; i < needAdd; i++) {
                        repetitiveBox.after(repetitiveBox.clone().cloneRow())
                    }
                }
                $('*[name="' + name + '"]', form).each(function(i){
                    this.addEventListener('keydown', function(e) { e.stopPropagation(); }, false);
                    if (typeof val[i] === 'string') {
                        val[i] = val[i].replace(/\&rsquo\;/g, "'").replace(/\&rdquo\;/g, "\"");
                    }
                    $(this).val(val[i])
                })
            } else {
                this.addEventListener('keydown', function(e) { e.stopPropagation(); }, false);
                if (typeof val === 'string') {
                    val = val.replace(/\&rsquo\;/g, "'").replace(/\&rdquo\;/g, "\"");
                }
                $(this).val(val);
            }

        });

        form.on('submit', function(e){
            e.preventDefault();

            save();

            return false;
        });

        $('.popup-tabs-wrap').addClass('tabbable tabbable-custom box-style-tab');
        $('.popup-tabs').addClass('nav nav-tabs nav-tabs-scroll style-tabs');
        $('.popup-tab-content').addClass('tab-content');
        $('.popup-tab-pane').addClass('tab-pane');

        $('.nav-tabs-scroll').scrollingTabs();

        $('.nav-tabs a').on('click', function(){
            $('.popup-tab-pane').removeClass('active');
            let href = $(this).attr('href');
            $(''+href).addClass('active')
        });



        let linkToPage = $('.link-to-page');
        let linkDefault = $('.link-default');
        let linkToProduct = $('.link-to-product');
        let linkToCategory = $('.link-to-category');
        let linkToInfo = $('.link-to-info');
        let linkToBrand = $('.link-to-brand');
        let linkToDelivery = $('.link-to-delivery');
        let linkToCommon = $('.link-to-common');
        let selectLinkType = function(){
            if (!$(this).val()) $(this).val('link-to-product');
            linkToPage.hide();
            switch ($(this).val()) {
                case 'link-to-category':
                    linkToCategory.show();
                    break;
                case 'link-to-info':
                    linkToInfo.show();
                    break;
                case 'link-default':
                    linkDefault.show();
                    break;
                case 'link-to-brand':
                    linkToBrand.show();
                    break;
                case 'link-to-delivery':
                    linkToDelivery.show();
                    break;
                case 'link-to-common':
                    linkToCommon.show();
                    break;
                case 'link-to-product':
                    linkToProduct.show();
                    break;
            }
        };
        $('select[name="link"]')
            .each(selectLinkType)
            .on('change', selectLinkType);

        linkDefault.show();
        linkToCommon.show();
        setTimeout(function(){
            $('select[name="link"]')
                .each(selectLinkType)
        }, 100)


        $('.product-name').searchProduct({
            suggestItemClick: function(input, itemObj){
                $('input[name="products_id"]', input.closest('.row')).val(itemObj.data('id'));
            }
        });

        $('.category-name').searchCategory({
            suggestItemClick: function(input, itemObj){
                $('input[name="categories_id"]', input.closest('.row')).val(itemObj.data('id'));
            }
        });

        $('.information-name').searchInfo({
            suggestItemClick: function(input, itemObj){
                $('input[name="information_id"]', input.closest('.row')).val(itemObj.data('id'));
            }
        });

        $('.brand-name').searchInfo({
            url: 'categories/brands-list',
            suggestItemClick: function(input, itemObj){
                $('input[name="brand_id"]', input.closest('.row')).val(itemObj.data('id'));
            }
        });

        $('.delivery-name').searchCategory({
            url: 'seo-delivery-location/all-list',
            suggestItemClick: function(input, itemObj){
                $('input[name="delivery_id"]', input.closest('.row')).val(itemObj.data('id'));
            }
        });

        $('.btn-add-more').on('click', function(){
            let repetitiveBox = $('.'+$(this).data('repeat') + ':last');
            let clone = repetitiveBox.clone().cloneRow();
            $('.product-name', clone).searchProduct({
                suggestItemClick: function(input, itemObj){
                    $('input[name="products_id"]', clone).val(itemObj.data('id'));
                }
            });
            $('.category-name', clone).searchCategory({
                suggestItemClick: function(input, itemObj){
                    $('input[name="categories_id"]', clone).val(itemObj.data('id'));
                }
            });
            $('.information-name', clone).searchInfo({
                suggestItemClick: function(input, itemObj){
                    $('input[name="information_id"]', clone).val(itemObj.data('id'));
                }
            });
            $('.brand-name', clone).searchInfo({
                suggestItemClick: function(input, itemObj){
                    url: 'categories/brands-list',
                    $('input[name="brand_id"]', clone).val(itemObj.data('id'));
                }
            });
            $('.delivery-name', clone).searchCategory({
                url: 'seo-delivery-location/all-list',
                suggestItemClick: function(input, itemObj){
                    $('input[name="delivery_id"]', clone).val(itemObj.data('id'));
                }
            });
            repetitiveBox.after(clone)
        })


    };

    $('<a href="#svg-item-attributes"></a>').popUp({
        close:  function(){
            $('.popup-box').on('click', '.btn-cancel, .pop-up-close', function(){
                popupTemplateHolder.html(popupTemplate);
                unload();
                return false
            });
        },
        box_class: 'widget-settings',
        loaded: popupContent
    }).click();

    $(window).on('popupUnload', function(){
        if (popupTemplate) {
            popupTemplateHolder.html(popupTemplate);
        }
    })

};
export default attributesPopup;
