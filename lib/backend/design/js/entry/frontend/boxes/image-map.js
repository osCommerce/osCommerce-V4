let compressionRatio = 1;

let countCompressionRatio = function(){
    let img = $('.svg-wrap img');
    let widthCurrent = img.width();
    img.css({
        'position': 'absolute',
        'width': 'auto',
        'height': 'auto',
        'max-width': 'none',
        'max-height': 'none',
    });
    let widthOrigin = img.width();
    img.css({
        'position': '',
        'width': '',
        'height': '',
        'max-width': '',
        'max-height': '',
    });
    compressionRatio = widthCurrent/widthOrigin;
};
countCompressionRatio();
$(window).on('resize', countCompressionRatio);

let prepareHoverItems = function() {
    let productItems = $('.products-items .item');
    let categoriesItems = $('.categories-items .item');
    let infoItems = $('.info-items .item');
    let brandsItems = $('.brands-items .item');
    let locationsItems = $('.locations-items .item');
    let defaultsItems = $('.default-links .item');
    let commonItems = $('.common-links .item');

    //remove styles for elements which wrap products
    let wrapper = productItems.parent();
    let fuse = 0;
    while (!wrapper.hasClass('products-items') && fuse < 20){
        fuse++;
        wrapper.css({
            'margin': '0',
            'padding': '0',
            'border': 'none',
            'box-shadow': 'none',
            'background': 'none',
            'overflow': 'visible',
            'position': 'static'
        });
        wrapper = wrapper.parent()
    }

    let products = {};
    productItems.each(function(){
        products[$(this).data('id')] = $(this);
    });

    let defaults = {};
    defaultsItems.each(function(){
        defaults[$(this).data('id')] = $(this);
    });

    let categories = {};
    categoriesItems.each(function(){
        categories[$(this).data('id')] = $(this);
    });

    let info = {};
    infoItems.each(function(){
        info[$(this).data('id')] = $(this);
    });

    let brands = {};
    brandsItems.each(function(){
        brands[$(this).data('id')] = $(this);
    });

    let locations = {};
    locationsItems.each(function(){
        locations[$(this).data('id')] = $(this);
    });

    let common = {};
    commonItems.each(function(){
        common[$(this).data('id')] = $(this);
    });


    return {
        products: products,
        defaults: defaults,
        categories: categories,
        info: info,
        brands: brands,
        locations: locations,
        common: common,
    }
};

function findLimitPositions(s, previousItems){
    let gTop = s.top;
    let gLeft = s.left;
    let gRight = s.left + s.width;
    let gBottom = s.top + s.height;

    previousItems.forEach(function(item){
        let itemTop = item.offset().top;
        let itemLeft = item.offset().left;
        let itemRight = itemLeft + item.width() + parseInt(item.css('padding-left')) + parseInt(item.css('padding-right'));
        let itemBottom = itemTop + item.height() + parseInt(item.css('padding-top')) + parseInt(item.css('padding-bottom'));

        if (itemTop < gTop) {
            gTop = itemTop;
        }
        if (itemLeft < gLeft) {
            gLeft = itemLeft;
        }
        if (itemRight > gRight) {
            gRight = itemRight;
        }
        if (itemBottom > gBottom) {
            gBottom = itemBottom;
        }
    });

    return {
        top: gTop,
        left: gLeft,
        right: gRight,
        bottom: gBottom,
    }
}

function findEmptyArea(svgPosition, width, height) {

    let quotient = [
        ['top', (svgPosition.sTop - svgPosition.wScroll) / height],
        ['left', svgPosition.sLeft / width],
        ['right', (svgPosition.wWidth - svgPosition.sLeft - svgPosition.sWidth) / width],
        ['bottom', (svgPosition.wHeight - (svgPosition.sTop - svgPosition.wScroll + svgPosition.sHeight)) / height],
    ];

    let quotientMax = 0;
    let emptyArea = '';

    quotient.forEach(function([key, item]){
        if (item > quotientMax) {
            quotientMax = item;
            emptyArea = key;
        }
    });

    if (quotientMax < 1) {
        return false
    }

    let areaWidth = 0;
    let areaHeight = 0;

    switch (emptyArea) {
        case 'top':
            areaWidth = svgPosition.wWidth;
            areaHeight = svgPosition.sTop - svgPosition.wScroll;
            break;
        case 'left':
            areaWidth = svgPosition.sLeft;
            areaHeight = svgPosition.wHeight;
            break;
        case 'right':
            areaWidth = svgPosition.wWidth - svgPosition.sLeft - svgPosition.sWidth;
            areaHeight = svgPosition.wHeight;
            break;
        case 'bottom':
            areaWidth = svgPosition.wWidth;
            areaHeight = svgPosition.wHeight - (svgPosition.sTop - svgPosition.wScroll + svgPosition.sHeight)
            break;
    }

    return [emptyArea, areaWidth, areaHeight];
}

function schemaSize(linkedHoverItems, countsInRow) {
    let maxWidth = 0;
    let maxHeight = 0;
    let heights = [0, 0, 0, 0];

    linkedHoverItems.forEach(function (item, key) {
        heights[key % countsInRow] += item.size.height;
        if (key < countsInRow) {
            maxWidth += item.size.width
        }
    });
    heights.forEach(function (height) {
        if (height > maxHeight) maxHeight = height
    });

    return [maxWidth, maxHeight]
}

function chooseHoverItemPosition(svgPosition, linkedHoverItems) {
    let countItems = linkedHoverItems.length;
    let maxWidth = 0;
    let maxHeight = 0;
    let check;

    linkedHoverItems.forEach(function (hoverItem) {
        if (hoverItem.size.height > maxWidth) {
            maxWidth = hoverItem.size.width;
        }
        if (hoverItem.size.height > maxHeight) {
            maxHeight = hoverItem.size.height;
        }
    });

    let [position, areaWidth, areaHeight] = findEmptyArea(svgPosition, maxWidth, maxHeight);
    if (!position) {
        return [position, '3']
    }

    let [maxWidth1, maxHeight1] = schemaSize(linkedHoverItems, 1);
    let [maxWidth2, maxHeight2] = schemaSize(linkedHoverItems, 2);
    let [maxWidth3, maxHeight3] = schemaSize(linkedHoverItems, 3);
    let [maxWidth4, maxHeight4] = schemaSize(linkedHoverItems, 4);
    let [maxWidth5, maxHeight5] = schemaSize(linkedHoverItems, 5);
    let [maxWidth6, maxHeight6] = schemaSize(linkedHoverItems, 6);

    if (countItems <= 3) {
        if (maxHeight > maxWidth && areaWidth > maxWidth3) {
            return [position, '3']
        } else {
            return [position, '1']
        }
    }

    if (countItems <= 4) {
        if (areaWidth > maxWidth2 && areaHeight > maxHeight2) {
            return [position, '2']
        } else if ((position === 'top' || position === 'bottom') && areaWidth > maxWidth4) {
            return [position, '4']
        } else if (areaWidth > maxWidth2) {
            return [position, '2']
        } else {
            return [position, '1']
        }
    }

    if (countItems <= 6) {
        if (maxHeight < maxWidth && areaWidth > maxWidth2 && areaHeight > maxHeight2) {
            return [position, '2']
        } else if (maxHeight >= maxWidth && areaWidth > maxWidth3 && areaHeight > maxHeight3) {
            return [position, '3']
        } else if (areaWidth > maxWidth6 && areaHeight > maxHeight6) {
            return [position, '6']
        } else if (maxHeight < maxWidth && areaWidth > maxWidth2) {
            return [position, '2']
        } else if (maxHeight >= maxWidth && areaWidth > maxWidth3) {
            return [position, '3']
        }
        return [position, '2']
    }

    if (countItems > 6) {
        if (areaWidth > maxWidth4) {
            return [position, '4']
        } else if (areaWidth > maxWidth3) {
            return [position, '3']
        } else {
            return [position, '2']
        }
    }

    return ['bottom', 'allWidth']

}

function getHoverItemSize(hoverItem) {
    return {
        width: hoverItem.width() + parseInt(hoverItem.css('padding-left')) + parseInt(hoverItem.css('padding-right')),
        height: hoverItem.height() + parseInt(hoverItem.css('padding-top')) + parseInt(hoverItem.css('padding-bottom'))
    }
}

function hideHoverItems(hoverItems){
    for (let type in hoverItems) {
        if (!hoverItems.hasOwnProperty( type )) continue;

        for (let id in hoverItems[type]) {
            if (!hoverItems[type].hasOwnProperty( id )) continue;

            hoverItems[type][id].css('display', '');
        }
    }
}

function getSvgPosition(svgItem) {

    let wScroll = $(window).scrollTop(),
        wWidth = $(window).width(),
        wHeight = $(window).height(),
        sLeft = svgItem.offset().left,
        sTop = svgItem.offset().top,
        sWidth = 0,
        sHeight = 0;

    if (svgItem.get(0).tagName === 'rect'){

        sWidth = svgItem.width() * compressionRatio;
        sHeight = svgItem.height() * compressionRatio;

    } else if (svgItem.get(0).tagName === 'circle'){

        sWidth = sHeight = svgItem.attr('r') * 2 * compressionRatio;

    } else if (svgItem.get(0).tagName === 'polygon') {

        let points = svgItem.attr('points').trim().replace( /[\s]+/g, " " ).split(' ');

        let left = 100000,
            right = 0,
            top = 100000,
            bottom = 0;
        points.forEach(function(item, key){
            item = item*1;
            if (key%2 === 0) {
                left = (item < left ? item : left);
                right = (item > right ? item : right);
            } else {
                top = (item < top ? item : top);
                bottom = (item > bottom ? item : bottom);
            }
        });

        sWidth = (right - left) * compressionRatio;
        sHeight = (bottom - top) * compressionRatio;
    }

    return {
        sLeft: sLeft,
        sTop: sTop,
        sWidth: sWidth,
        sHeight: sHeight,
        wScroll: wScroll,
        wWidth: wWidth,
        wHeight: wHeight,
    };

}

function getTopLeftPosition(position, svgPosition, maxWidth, maxHeight) {
    let top;
    let left;

    switch (position) {
        case 'top':
            left = svgPosition.sLeft + svgPosition.sWidth/2 - maxWidth/2;
            top = svgPosition.sTop - maxHeight;
            break;
        case 'left':
            left = svgPosition.sLeft - maxWidth;
            top = svgPosition.sTop + svgPosition.sHeight/2 - maxHeight/2;
            break;
        case 'right':
            left = svgPosition.sLeft + svgPosition.sWidth;
            top = svgPosition.sTop + svgPosition.sHeight/2 - maxHeight/2;
            break;
        case 'bottom':
            left = svgPosition.sLeft + svgPosition.sWidth/2 - maxWidth/2;
            top = svgPosition.sTop + svgPosition.sHeight
            break;
        default:
            let countsInRow = Math.floor(svgPosition.wWidth / maxWidth);
            left = svgPosition.wWidth/2 - (countsInRow * maxWidth / 2);
            top = svgPosition.sTop + svgPosition.sHeight;
    }

    top = top - svgPosition.wScroll;

    if (left < 0) left = 0;
    if (left + maxWidth > svgPosition.wWidth) left = svgPosition.wWidth - maxWidth;
    if (top < 0) top = 0;

    return [top, left]
}

function setHoverItemPosition(svgPosition, linkedHoverItems, position, schema){
    let countsInRow = schema;
    let maxWidth;
    let maxHeight;
    if (countsInRow === 0){
        maxWidth = linkedHoverItems[0].size.width;
        maxHeight = linkedHoverItems[0].size.height;
        countsInRow = Math.floor(svgPosition.wWidth / maxWidth);
    } else {
        [maxWidth, maxHeight] = schemaSize(linkedHoverItems, countsInRow);
    }
    let [top, left] = getTopLeftPosition(position, svgPosition, maxWidth, maxHeight);

    let tops = [top, top, top, top, top, top];
    let leftTmp = left;

    linkedHoverItems.forEach(function (item, key) {
        if (key % countsInRow === 0){
            leftTmp = left;
        }
        item.item.css({
            left: leftTmp,
            top: tops[key % countsInRow]
        });
        leftTmp += item.size.width;

        tops[key % countsInRow] += item.size.height;
    })

}

function itemPositionScroll(event){
    event.data.item.css({
        top: event.data.top - $(window).scrollTop() + event.data.scrollTop
    });
};

let overSvgItems = function(svgItems, hoverItems){
    let showingKey = {};
    svgItems.hover(function(){
        let type = $(this).data('type');
        let id = $(this).data('id');
        let ids = typeof id === "string" ? id.split(',') : [id];
        let linkedHoverItems = [];
        let uniqueArray = [];

        hideHoverItems(hoverItems);

        showingKey[type+id] = true;
        ids.forEach(id => {
            if (uniqueArray.includes(id)) {
                return '';
            }
            uniqueArray.push(id);
            let hoverItem = hoverItems[type][id];
            if (hoverItem) {
                hoverItem.css('display', 'block');
                linkedHoverItems.push({item: hoverItem, size: getHoverItemSize(hoverItem)});
            }
        });

        let svgPosition = getSvgPosition($(this));
        let [position, schema] = chooseHoverItemPosition(svgPosition, linkedHoverItems);

        setHoverItemPosition(svgPosition, linkedHoverItems, position, schema);

        $(window).off('scroll', itemPositionScroll);

        linkedHoverItems.forEach(function (item) {
            let top = parseInt(item.item.css('top'));
            let scrollTop = $(window).scrollTop();

            $(window).on('scroll', {
                item: item.item,
                top: top,
                scrollTop: scrollTop
            }, itemPositionScroll)
        })

    }, function(){
        let type = $(this).data('type');
        let id = $(this).data('id');
        let ids = typeof id === "string" ? id.split(',') : [id];
        let showingKeyId = type+id;
        showingKey[showingKeyId] = false;
        setTimeout(function(){
            ids.forEach(id => {
                if (!showingKey[showingKeyId] && hoverItems[type][id]) {
                    hoverItems[type][id].css('display', '');
                }
            });
        }, 500)
    });

    svgItems.each(function(){
        let type = $(this).data('type');
        let id = $(this).data('id');
        let ids = typeof id === "string" ? id.split(',') : [id];
        let showingKeyId = type+id;
        let noItems = true;

        ids.forEach(function(id){
            if (!hoverItems[type][id]) {
                return '';
            }
            noItems = false;
            hoverItems[type][id].hover(function(){
                showingKey[showingKeyId] = true;
            }, function(){
                showingKey[showingKeyId] = false;
                setTimeout(function(){
                    ids.forEach(id => {
                        if (!showingKey[showingKeyId] && hoverItems[type][id]) {
                            hoverItems[type][id].css('display', '');
                        }
                    });
                }, 500)
            })
        })
        if (noItems) {
            $(this).addClass('no-items')
        }
    })
};


export function imageMap(allItemsJson) {

    let allItems = JSON.parse(allItemsJson);

    let widget = $('.w-image-maps');
    let svg = $('svg', widget);
    let svgItems = $('*', svg);

    let hoverItems = prepareHoverItems();

    overSvgItems(svgItems, hoverItems);
}