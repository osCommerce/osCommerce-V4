let searchProduct = function(options){
    let op = $.extend({
        url: 'index/search-suggest',
        suggestWrapClass: 'search-product',
        suggestClass: 'suggest',
        suggestItem: 'a',
        itemTextBox: '.td_name',
        suggestItemClick: function(input, itemObj){},
    },options);

    return this.each(function() {

        let searchInput = $(this);
        let suggestWrap = $(`<div class="${op.suggestWrapClass}"></div>`);
        let suggest = $(`<div class="${op.suggestClass}"></div>`);

        searchInput.after(suggestWrap);
        suggestWrap.append(suggest);
        suggest.hide();

        searchInput.on('keyup', function(e){
            $.get(op.url, {
                keywords: searchInput.val(),
                no_click: true
            }, function(data){
                suggest.show().html(data);
            })
        });

        suggest.on('click', op.suggestItem, function(e){
            e.preventDefault();

            searchInput.val($(op.itemTextBox, this).text());
            suggest.hide().html('');

            op.suggestItemClick(searchInput, $(this));
            searchInput.trigger('suggestItemClick', [searchInput, $(this)]);

            return false
        });

        searchInput.on('blur', function(){
            setTimeout(function() {
                suggest.hide();
            }, 300);
        })
    })
};
export default searchProduct;