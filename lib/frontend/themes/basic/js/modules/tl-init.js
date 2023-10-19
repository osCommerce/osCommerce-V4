if (!Object.assign) {
    Object.defineProperty(Object, 'assign', {
        enumerable: false,
        configurable: true,
        writable: true,
        value: function(target, firstSource) {
            'use strict';
            if (target === undefined || target === null) {
                throw new TypeError('Cannot convert first argument to object');
            }

            var to = Object(target);
            for (var i = 1; i < arguments.length; i++) {
                var nextSource = arguments[i];
                if (nextSource === undefined || nextSource === null) {
                    continue;
                }

                var keysArray = Object.keys(Object(nextSource));
                for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
                    var nextKey = keysArray[nextIndex];
                    var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
                    if (desc !== undefined && desc.enumerable) {
                        to[nextKey] = nextSource[nextKey];
                    }
                }
            }
            return to;
        }
    });
}

tl.reducers = {};
function tl_action(script) {
    if (typeof jQuery == 'function') {
        tl_start = true;
        var action = function (block) {
            var key = true;
            $.each(block.js, function (j, js) {
                var include_index = tl_include_js.indexOf(js);
                if (include_index == -1 || tl_include_loaded.indexOf(js) == -1) {
                    key = false;
                }
            });
            if (key && block && typeof block.script === "function") {
                if (typeof requestIdleCallback === "function"){
                    requestIdleCallback(block.script);
                } else {
                    block.script()
                }
            }
            return key
        };
        $.each(script, function (i, block) {
            if (!action(block)) {
                $.each(block.js, function (j, js) {
                    var include_index = tl_include_js.indexOf(js);
                    if (include_index == -1) {
                        tl_include_js.push(js);
                        include_index = tl_include_js.indexOf(js);
                        $.ajax({
                            url: js, success: function () {
                                tl_include_loaded.push(js);
                                $(window).trigger('tl_action_' + include_index);
                            },
                            error: function (a, b, c) {
                                console.error('Error: "' + js + '" ' + c);
                            },
                            dataType: 'script',
                            cache: true
                        });
                    }
                    $(window).on('tl_action_' + include_index, function () {
                        action(block)
                    })
                })
            }
        })
    } else {
        setTimeout(function () {
            tl_action(script)
        }, 100)
    }
    document.cookie = "xwidth="+window.outerWidth;
    document.cookie = "xheight="+window.outerHeight;
};

tl(createJsUrl('main.js'), function(){
    $('.footerTitle, .gift-code .heading-4').off('click').click(function(){
        if($(window).width() >= 720) return;
        $(this).toggleClass('active');
        $('~ *', this).slideToggle();
    });
});
