$.fn.menuSlider = function( options ) {
    var settings = $.extend({
        holder: $('.dropdown'),
        close: 'Close'
    }, options );

    return this.each(function() {
        var ico = $(this);

        settings.holder.wrap( '<div class="menu-slider"></div>' );
        var slider = settings.holder.parent();
        slider.append('<div class="close-bar"></div>');
        settings.holder.addClass('menu-slider-holder');
        settings.holder.prepend('<div class="close">'+settings.close+'</div>');


        var showSlider = function(e){
            e.preventDefault();

            ico.addClass('active');
            slider.addClass('open')
        };

        var hideSlider = function(){
            ico.removeClass('active');

            slider.removeClass('open');
            slider.addClass('closing');
            setTimeout(function(){
                slider.removeClass('closing');
            }, 1000)
        };

        var closeBtn = $('.close', slider);
        var closeBar = $('.close-bar', slider);
        closeBtn.on('click',hideSlider);
        closeBar.on('click', hideSlider);

        ico.on('click', showSlider);
        ico.on('open', showSlider);
        ico.on('close', hideSlider);

        ico.on('remove', function(){
            $('.close-bar', slider).off('click',hideSlider);
            $('.close', slider).off('click', hideSlider);
            ico.off('click', showSlider);
            ico.off('open', showSlider);
            ico.off('close', hideSlider);

            closeBtn.remove();
            closeBar.remove();
            ico.removeClass('active');
            settings.holder.removeClass('menu-slider-holder');
            settings.holder.unwrap();
        });
    })
};