
$.fn.setStateCountryDependency = function(options){
        var _options = $.extend({
            'country' : options.country,
            'url': options.url,
            'appendto':options.appendto
        }, options);
        return this.each(function() {
            var that = this;            
            $(that).autocomplete({
              source: function(request, response) {
                if ( $(_options.country).val() > 0 ) {
                  $.getJSON(_options.url, { term : request.term, country: $(_options.country).val() }, response);
                }
              },
              minLength: 0,
              autoFocus: true,
              appendTo:_options.appendto,
              //delay: 0,
              open: function (e, ui) {
                if ($(this).val().length > 0) {
                  var acData = $(this).data('ui-autocomplete');
                  acData.menu.element.find('a').each(function () {
                    var me = $(this);
                    var keywords = acData.term.split(' ').join('|');
                    me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                  });
                }
              },
              response: function( event, ui ) {
                $(that).attr('autocomplete', (ui.content.length > 0? 'nope': 'off'));
              },
              select: function( event, ui ) {
                setTimeout(function(){
                  $(that).trigger('change');
                }, 200);
              }
            }).focus(function () {
              $(that).autocomplete('search','');
            });
            
        });
        
    };
    
    $.fn.getCityList = function(options){
        var _options = $.extend({
            'country' : options.country,
            'state' : options.state,
            'url': options.url,/*'account/address-city'*/
            'appendto':options.appendto
        }, options);
        return this.each(function() {
            var that = this;            
            $(that).autocomplete({
              source: function(request, response) {
                  if ( $(_options.country).val() > 0 ) {
                      if (_options.state && $(_options.state).length>0){
                          $.getJSON(_options.url, {term: request.term, country: $(_options.country).val(), 'state': $(_options.state).val()}, response);
                      }else {
                          $.getJSON(_options.url, {term: request.term, country: $(_options.country).val()}, response);
                      }
                  }
              },
              minLength: 0,
              autoFocus: true,
              appendTo:_options.appendto,
              //delay: 0,
              open: function (e, ui) {
                if ($(this).val().length > 0) {
                  var acData = $(this).data('ui-autocomplete');
                  acData.menu.element.find('a').each(function () {
                    var me = $(this);
                    var keywords = acData.term.split(' ').join('|');
                    me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                  });
                }
              },
              response: function( event, ui ) {
                $(that).attr('autocomplete', (ui.content.length > 0? 'nope': 'off'));
              },
              select: function( event, ui ) {
                setTimeout(function(){
                    if ( ui.item.state && _options.state && $(_options.state).length>0 ){
                        $(_options.state).val( ui.item.state );
                        $(_options.state).trigger('change');
                    }
                    $(that).trigger('change');
                }, 200);
              }
            }).focus(function () {
                if($(_options.country).val()>0){
                    $(that).autocomplete('search','');
                }
            }).data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
                var address = [];
                if ( item.city ) { address.push(item.city); }
                if ( item.state ) { address.push(item.state); }
                return $( '<li></li>' )
                    .data( 'item.autocomplete', item )
                    //.append( '<div>' + item.label + '<br><span class="post-code-complete-address">' + address.join(', ') + '</div>' )
                    .append( '<a>' + item.label + '</a><br><span class="post-code-complete-address">' + address.join(', ') + '' )
                    .appendTo( ul );
            };
        });
    };

    $.fn.getPostcodeList = function(options){
        var _options = $.extend({
            'country' : options.country,
            'state': options.state,
            'city': options.city,
            'suburb': options.suburb,
            'url': options.url,/*'account/address-city'*/
            'appendto':options.appendto
        }, options);
        return this.each(function() {
            var that = this;
            $(that).autocomplete({
                source: function(request, response) {
                    $.getJSON(_options.url, { term : request.term, country: $(_options.country).val() }, response);
                },
                minLength: 2,
                autoFocus: true,
                appendTo:_options.appendto,
                delay: 0,
                open: function (e, ui) {
                    if ($(this).val().length > 0) {
                        var acData = $(this).data('ui-autocomplete');
                        acData.menu.element.find('a').each(function () {
                            var me = $(this);
                            var keywords = acData.term.split(' ').join('|');
                            me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                        });
                    }
                },
                response: function( event, ui ) {
                    $(that).attr('autocomplete', (ui.content.length > 0? 'nope': 'off'));
                },
                select: function( event, ui ) {
                    var item = ui.item;
                    $(that).val( item.label );
                    $(that).trigger('change');

                    if ( item.state && _options.state && $(_options.state).length>0 ){
                        $(_options.state).val( item.state );
                        $(_options.state).trigger('change');
                    }
                    if ( item.city && _options.city && $(_options.city).length>0 ){
                        $(_options.city).val( item.city );
                        $(_options.city).trigger('change');
                    }
                    if ( item.suburb && _options.suburb && $(_options.suburb).length>0 ){
                        $(_options.suburb).val( item.suburb );
                        $(_options.suburb).trigger('change');
                    }
                    //return false;
                }
            }).focus(function () {
                $(that).autocomplete('search');
            }).data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
                var address = [];
                if ( item.suburb ) { address.push(item.suburb); }
                if ( item.city ) { address.push(item.city); }
                if ( item.state ) { address.push(item.state); }
                return $( '<li>' )
                    //.append( '<div>' + item.label + '<br><span class="post-code-complete-address">' + address.join(', ') + '</div>' )
                    .append( '<a>' + item.label + '</a><br><span class="post-code-complete-address">' + address.join(', ') + '' )
                    .appendTo( ul );
            };
        });
    };
