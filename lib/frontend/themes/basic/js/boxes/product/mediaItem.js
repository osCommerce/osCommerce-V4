if (!Product) var Product = {};
Product.mediaItem = function(image, size, id = '') {

    var width = 120;
    var height = 80;
    if (size === 'Large') {
        width = 900;
        height = 600;
    }
    var $media = '';
    if (image.type === 'image') {
        $media = $('<img>', {
            'src': image.image[size].url,
            'data-zoom-image': image.image[size].url,
            'alt': image.alt,
            'title': image.title,
        });
        if (size === 'Large') {
            $media.on('mousemove', function(e){
                if (
                    $media[0].clientHeight < $media[0].naturalHeight &&
                    $media[0].clientWidth < $media[0].naturalWidth
                ) {
                    var kLeft = e.offsetX / $media[0].clientHeight;
                    var kTop = e.offsetY / $media[0].clientHeight;
                    var left = -($media[0].naturalWidth - $media[0].clientWidth) * kLeft;
                    var top = -($media[0].naturalHeight - $media[0].clientHeight) * kTop;
                    $media.css({
                        'object-fit': 'none',
                        'object-position': left + 'px ' + top + 'px',
                    })
                }
            })
            $media.on('mouseout', function(){
                $media.css({
                    'object-fit': 'fill',
                    'object-position': '0 0',
                })
            })
        }
    } else if (image.video_type == 0 && image.code) {
        if (size === 'Large') {
            $media = $('<iframe>', {
                width: width,
                height: height,
                src: "https://www.youtube.com/embed/" + image.code + "?rel=1controls=1&showinfo=1",
                frameborder: "0",
                allowfullscreen: 'allowfullscreen',
            });
        } else {
            $media = $('<img>', {
                'src': 'https://img.youtube.com/vi/' + image.code + '/0.jpg',
                'alt': image.alt,
                'title': image.title,
            });
        }
    } else if (image.video_type == 1) {
        $media = $('<video class="video-js" width="' + width + 'px" height="' + height + 'px" ' + (size === 'Large' ? 'controls' : '') + '>\
                            <source src="' + image.src + '">\
                          </video>');
    }
    $item = $('<div class="item" data-id="' + id + '"></div>');
    $item.append($media)
    return $item;
}