let searchCategory = function(options){
    let op = $.extend({
        url: 'categories/all-list',
        suggestWrapClass: 'search-category',
        suggestClass: 'suggest',
        suggestItem: '.item',
        itemTextBox: '.name',
        suggestItemClick: function(input, itemObj){},
    },options);

    return this.each(function() {

        let searchInput = $(this);
        let suggestWrap = $(`<div class="${op.suggestWrapClass}"></div>`);
        let suggest = $(`<div class="${op.suggestClass}"></div>`);

        searchInput.after(suggestWrap);
        suggestWrap.append(suggest);
        suggest.hide();

        searchInput.on('focus', function(e){
            $.get(op.url, {
                keywords: searchInput.val(),
                no_click: true
            }, function(data){
                for (let key in data){
                    if (!data.hasOwnProperty(key)) continue;

                    let itemHtml = `<div class="item" data-id="${data[key].id}">
                                        <div class="name">${data[key].text}</div>
                                        <div class="sub-categories" id="sc-${data[key].id}"></div>
                                    </div>`;
                    if (data[key].parent_id == 0) {
                        suggest.append(itemHtml);
                    } else {
                        $('#sc-' + data[key].parent_id, suggest).append(itemHtml)
                    }
                }
                suggest.show()
            }, 'json')
        });

        searchInput.on('keyup', function(e){
            let search = '('  + searchInput.val() + ')';
            let re = new RegExp(search,"gi");

            $('.item', suggest).each(function(){
                let name = $('> .name', this);
                let text = name.text().trim();
                let serched = text.replace(re, '<span class="keyword">$1</span>');
                name.html(serched);
                if (text.length !== serched.length) {
                    $(this).show();
                    $(this).parents('.item').show();
                } else {
                    $(this).hide()
                }
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
export default searchCategory;