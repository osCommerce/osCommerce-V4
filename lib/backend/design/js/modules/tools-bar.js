let tools = function() {
    let toolsBar = $('.tools-bar');
    let toolsBar2 = $('.container');
    let toolsBarWrap = $('.tools-bar-wrap');
    let topHeadingBar = $('.top_bead');

    let limitTop = 0,
        toolsBarMainPosition = {},
        toolsBarMainWidth = 0;

    let windowScroll = function () {

        if (toolsBarMainPosition.top - limitTop <= $(window).scrollTop()) {
            toolsBar.css({
                'position': 'fixed',
                'left': toolsBarMainPosition.left,
                'top': limitTop,
                'width': toolsBarMainWidth,
                'z-index': 100
            });
            toolsBar.addClass('fixed-bar');
        } else {
            toolsBar.css({
                'position': '',
                'left': '',
                'top': '',
                'width': '',
                'z-index': ''
            });
            toolsBar.removeClass('fixed-bar');
        }

    };

    let windowResize = function(){
        toolsBar.css({
            'position': '',
            'left': '',
            'top': '',
            'width': '',
            'z-index': ''
        });
        toolsBar.removeClass('fixed-bar');

        limitTop = toolsBar2.height() + topHeadingBar.height() + 1;
        toolsBarMainPosition = toolsBar.offset();
        toolsBarMainWidth = toolsBar.width();
        toolsBarWrap.height(toolsBarWrap.height());

        windowScroll();
    };
    windowResize();


    $(window).on('scroll', windowScroll);
    $(window).on('resize', windowResize)
};

export default tools;