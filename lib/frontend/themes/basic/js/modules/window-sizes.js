var tlSize = {
    current: [],
    dimensions: [],

    init: function(){
        tlSize.dimensions = entryData.layoutSizes;
        $(window).on('layoutChange', tlSize.bodyClass);
        tlSize.resize();
        $(window).on('resize', tlSize.resize);
    },

    resize: function(){
        $.each(tlSize.dimensions, function(key, val){
            var from = val[0]*1;
            var to = val[1];
            if (to) {
                to = to*1
            } else {
                to = 10000
            }
            var data = { };
            var w = window.innerWidth;
            if (!w) {
                w = $(window).width();
            }
            if (from <= w && w <= to) {
                if ($.inArray(key, tlSize.current ) === -1) {
                    tlSize.current.push(key);
                    tlSize.current = tlSize.sort(tlSize.current);
                    data = {
                        key: key,
                        status: 'in',
                        from: from,
                        to: to,
                        current: tlSize.current
                    };
                    $(window).trigger('layoutChange', [data]);
                    $(window).trigger(key+'in', [data]);
                }
            } else {
                var index = tlSize.current.indexOf(key);
                if (index > -1) {
                    tlSize.current.splice(index, 1);
                    tlSize.current = tlSize.sort(tlSize.current);
                    data = {
                        key: key,
                        status: 'out',
                        from: from,
                        to: to,
                        current: tlSize.current
                    };
                    $(window).trigger('layoutChange', [data]);
                    $(window).trigger(key+'out', [data]);
                }
            }
        })
    },

    sort: function(arr){
        var v = [];
        var t = [];
        var tmp = [];
        var l = arr.length;
        for (var i = 0; i < l; i++) {
            tmp[i] = '0w0';
            $.each(arr, function (key, val) {
                v = val.split('w');
                v[0] = v[0]*1;
                v[1] = v[1]*1;
                if (!v[1]) {
                    v[1] = 10000
                }
                t = tmp[i].split('w');
                t[0] = t[0]*1;
                t[1] = t[1]*1;
                if (t[1] < v[1]) {
                    tmp[i] = val
                } else if (t[1] == v[1] && t[0] > v[0]) {
                    tmp[i] = val
                }
            });
            var index = arr.indexOf(tmp[i]);
            arr.splice(index, 1);
        }

        return tmp
    },

    bodyClass: function(e, d){
        if (d.status == 'in') {
            $('body').addClass(d.key)
        }
        if (d.status == 'out') {
            $('body').removeClass(d.key)
        }
    }

};