let searchInfoPage = function(options){
    let op = $.extend({
        url: 'information_manager/all-list',
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
            e.preventDefault();

            $.get(op.url, {
                keywords: searchInput.val(),
                no_click: true
            }, function(data){
                for (let key in data){
                    if (!data.hasOwnProperty(key)) continue;

                    let itemHtml = `<div class="item" data-id="${data[key].id}">
                                        <div class="name">${data[key].text}</div>
                                    </div>`;
                    suggest.append(itemHtml);
                }
                suggest.show()
            }, 'json');

            return false
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
export default searchInfoPage;