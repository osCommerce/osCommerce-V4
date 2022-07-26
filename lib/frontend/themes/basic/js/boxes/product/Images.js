tl([
    createJsUrl('slick.min.js'),
    //createJsUrl('jquery.fancybox.pack.js')
], function(){

    var productId = $('input[name="products_id"]').val();

    $('.w-product-images').each(function() {
        var $box = $(this);
        var widgetId = $box.attr('id').substring(4);

        tl.subscribe(['products', productId, 'defaultImage'], function(){
            var state = tl.store.getState();
            var image = state.products[productId].images[state.products[productId].defaultImage];

            if (!image) {
                image = state.products[productId].images[Object.keys(state.products[productId].images)[0]]
            }
            var $media = '';
            if (image.type === 'image') {
                $media = $('<img>', {
                    'src': image.image.Medium.url,
                    'data-lrg': image.image.Large.url,
                    'alt': image.alt,
                    'title': image.title,
                    'class': 'main-image zoom',
                });
            } else if (image.video_type == 0 && image.code) {
                $media = $('<iframe>', {
                    width: 560,
                    height: 315,
                    src:"https://www.youtube.com/embed/" + image.code + "?rel=1controls=1&showinfo=1",
                    frameborder: "0",
                    allowfullscreen: 'allowfullscreen',
                });
            } else if (image.video_type == 1) {
                $media = $('<video class="video-js" width="560px" height="315px" controls>\
                            <source src="' + image.src + '">\
                          </video>');
            }
            if ($media.length) {
                $('.img-holder', $box).html('').append($media);
            }
        });

        $('.img-holder', $box).on('click', 'img', function(){
            var state = tl.store.getState();
            var images = state.products[productId].images;
            var initialSlide = 0;

            for (var id in images) {
                if (state.products[productId].defaultImage == id) {
                    break
                }
                initialSlide++;
            }

            var $popUp = $('\
                <div class="mp-wrapper">\
                    <div class="mp-shadow"></div>\
                    <div class="media-popup">\
                        <div class="mp-close"></div>\
                        <div class="mp-content"></div>\
                     </div>\
                 </div>');

            $('.mp-close', $popUp).on('click', function(){
                $popUp.remove();
            })

            var $popUpContent = $('.mp-content', $popUp);

            var $bigImages = $('<div class="mp-big-images"></div>');
            var $smallImages = $('<div class="mp-small-images"></div>');

            for (var id in images) {
                $bigImages.append(Product.mediaItem(images[id], 'Large'));
                $smallImages.append(Product.mediaItem(images[id], 'Small'));
            }

            $popUpContent.append($bigImages);
            $popUpContent.append($smallImages);


            $('body').append($popUp)


            $bigImages.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                initialSlide: initialSlide,
                asNavFor: '.mp-small-images'
            });
            $smallImages.slick({
                slidesToShow: 9,
                slidesToScroll: 9,
                initialSlide: initialSlide,
                asNavFor: '.mp-big-images',
                dots: true,
                centerMode: true,
                focusOnSelect: true,
                responsive: [
                    {
                        breakpoint: 1500,
                        settings: {
                            slidesToShow: 7,
                            slidesToScroll: 7
                        }
                    },
                    {
                        breakpoint: 1100,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5
                        }
                    },
                    {
                        breakpoint: 700,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                ]
            })
        })
    })
})