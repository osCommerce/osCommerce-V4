/*!
 * JavaScript Cookie v2.0.3
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
(function (window, exports) {

// Public API
    exports.unserialize = unserialize;
    exports.unserializeSession = unserializeSession;
    exports.serialize = serialize;

    /**
     * Unserialize data taken from PHP's serialize() output
     *
     * Taken from https://github.com/kvz/phpjs/blob/master/functions/var/unserialize.js
     * Fixed window reference to make it nodejs-compatible
     *
     * @param string serialized data
     * @return unserialized data
     * @throws
     */
    function unserialize (data) {
        // http://kevin.vanzonneveld.net
        // +     original by: Arpad Ray (mailto:arpad@php.net)
        // +     improved by: Pedro Tainha (http://www.pedrotainha.com)
        // +     bugfixed by: dptr1988
        // +      revised by: d3x
        // +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +        input by: Brett Zamir (http://brett-zamir.me)
        // +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +     improved by: Chris
        // +     improved by: James
        // +        input by: Martin (http://www.erlenwiese.de/)
        // +     bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +     improved by: Le Torbi
        // +     input by: kilops
        // +     bugfixed by: Brett Zamir (http://brett-zamir.me)
        // +      input by: Jaroslaw Czarniak
        // %            note: We feel the main purpose of this function should be to ease the transport of data between php & js
        // %            note: Aiming for PHP-compatibility, we have to translate objects to arrays
        // *       example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}');
        // *       returns 1: ['Kevin', 'van', 'Zonneveld']
        // *       example 2: unserialize('a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}');
        // *       returns 2: {firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'}
        var that = this,
            utf8Overhead = function (chr) {
                // http://phpjs.org/functions/unserialize:571#comment_95906
                var code = chr.charCodeAt(0);
                if (code < 0x0080) {
                    return 0;
                }
                if (code < 0x0800) {
                    return 1;
                }
                return 2;
            },
            error = function (type, msg, filename, line) {
                throw new window[type](msg, filename, line);
            },
            read_until = function (data, offset, stopchr) {
                var i = 2, buf = [], chr = data.slice(offset, offset + 1);

                while (chr != stopchr) {
                    if ((i + offset) > data.length) {
                        error('Error', 'Invalid');
                    }
                    buf.push(chr);
                    chr = data.slice(offset + (i - 1), offset + i);
                    i += 1;
                }
                return [buf.length, buf.join('')];
            },
            read_chrs = function (data, offset, length) {
                var i, chr, buf;

                buf = [];
                for (i = 0; i < length; i++) {
                    chr = data.slice(offset + (i - 1), offset + i);
                    buf.push(chr);
                    length -= utf8Overhead(chr);
                }
                return [buf.length, buf.join('')];
            },
            _unserialize = function (data, offset) {
                var dtype, dataoffset, keyandchrs, keys,
                    readdata, readData, ccount, stringlength,
                    i, key, kprops, kchrs, vprops, vchrs, value,
                    chrs = 0,
                    typeconvert = function (x) {
                        return x;
                    };

                if (!offset) {
                    offset = 0;
                }
                dtype = (data.slice(offset, offset + 1)).toLowerCase();

                dataoffset = offset + 2;

                switch (dtype) {
                    case 'i':
                        typeconvert = function (x) {
                            return parseInt(x, 10);
                        };
                        readData = read_until(data, dataoffset, ';');
                        chrs = readData[0];
                        readdata = readData[1];
                        dataoffset += chrs + 1;
                        break;
                    case 'b':
                        typeconvert = function (x) {
                            return parseInt(x, 10) !== 0;
                        };
                        readData = read_until(data, dataoffset, ';');
                        chrs = readData[0];
                        readdata = readData[1];
                        dataoffset += chrs + 1;
                        break;
                    case 'd':
                        typeconvert = function (x) {
                            return parseFloat(x);
                        };
                        readData = read_until(data, dataoffset, ';');
                        chrs = readData[0];
                        readdata = readData[1];
                        dataoffset += chrs + 1;
                        break;
                    case 'n':
                        readdata = null;
                        break;
                    case 's':
                        ccount = read_until(data, dataoffset, ':');
                        chrs = ccount[0];
                        stringlength = ccount[1];
                        dataoffset += chrs + 2;

                        readData = read_chrs(data, dataoffset + 1, parseInt(stringlength, 10));
                        chrs = readData[0];
                        readdata = readData[1];
                        dataoffset += chrs + 2;
                        if (chrs != parseInt(stringlength, 10) && chrs != readdata.length) {
                            error('SyntaxError', 'String length mismatch');
                        }
                        break;
                    case 'a':
                        readdata = {};

                        keyandchrs = read_until(data, dataoffset, ':');
                        chrs = keyandchrs[0];
                        keys = keyandchrs[1];
                        dataoffset += chrs + 2;

                        for (i = 0; i < parseInt(keys, 10); i++) {
                            kprops = _unserialize(data, dataoffset);
                            kchrs = kprops[1];
                            key = kprops[2];
                            dataoffset += kchrs;

                            vprops = _unserialize(data, dataoffset);
                            vchrs = vprops[1];
                            value = vprops[2];
                            dataoffset += vchrs;

                            readdata[key] = value;
                        }

                        dataoffset += 1;
                        break;
                    default:
                        error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
                        break;
                }
                return [dtype, dataoffset - offset, typeconvert(readdata)];
            }
            ;

        return _unserialize((data + ''), 0)[2];
    }
    
    function serialize (mixedValue) {
      //  discuss at: https://locutus.io/php/serialize/
      // original by: Arpad Ray (mailto:arpad@php.net)
      // improved by: Dino
      // improved by: Le Torbi (https://www.letorbi.de/)
      // improved by: Kevin van Zonneveld (https://kvz.io/)
      // bugfixed by: Andrej Pavlovic
      // bugfixed by: Garagoth
      // bugfixed by: Russell Walker (https://www.nbill.co.uk/)
      // bugfixed by: Jamie Beck (https://www.terabit.ca/)
      // bugfixed by: Kevin van Zonneveld (https://kvz.io/)
      // bugfixed by: Ben (https://benblume.co.uk/)
      // bugfixed by: Codestar (https://codestarlive.com/)
      // bugfixed by: idjem (https://github.com/idjem)
      //    input by: DtTvB (https://dt.in.th/2008-09-16.string-length-in-bytes.html)
      //    input by: Martin (https://www.erlenwiese.de/)
      //      note 1: We feel the main purpose of this function should be to ease
      //      note 1: the transport of data between php & js
      //      note 1: Aiming for PHP-compatibility, we have to translate objects to arrays
      //   example 1: serialize(['Kevin', 'van', 'Zonneveld'])
      //   returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
      //   example 2: serialize({firstName: 'Kevin', midName: 'van'})
      //   returns 2: 'a:2:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";}'
      //   example 3: serialize( {'ü': 'ü', '四': '四', '𠜎': '𠜎'})
      //   returns 3: 'a:3:{s:2:"ü";s:2:"ü";s:3:"四";s:3:"四";s:4:"𠜎";s:4:"𠜎";}'

      var val, key, okey
      var ktype = ''
      var vals = ''
      var count = 0

      var _utf8Size = function (str) {
        return ~-encodeURI(str).split(/%..|./).length
      }

      var _getType = function (inp) {
        var match
        var key
        var cons
        var types
        var type = typeof inp

        if (type === 'object' && !inp) {
          return 'null'
        }

        if (type === 'object') {
          if (!inp.constructor) {
            return 'object'
          }
          cons = inp.constructor.toString()
          match = cons.match(/(\w+)\(/)
          if (match) {
            cons = match[1].toLowerCase()
          }
          types = ['boolean', 'number', 'string', 'array']
          for (key in types) {
            if (cons === types[key]) {
              type = types[key]
              break
            }
          }
        }
        return type
      }

      var type = _getType(mixedValue)

      switch (type) {
        case 'function':
          val = ''
          break
        case 'boolean':
          val = 'b:' + (mixedValue ? '1' : '0')
          break
        case 'number':
          val = (Math.round(mixedValue) === mixedValue ? 'i' : 'd') + ':' + mixedValue
          break
        case 'string':
          val = 's:' + _utf8Size(mixedValue) + ':"' + mixedValue + '"'
          break
        case 'array':
        case 'object':
          val = 'a'
          /*
          if (type === 'object') {
            var objname = mixedValue.constructor.toString().match(/(\w+)\(\)/);
            if (objname === undefined) {
              return;
            }
            objname[1] = serialize(objname[1]);
            val = 'O' + objname[1].substring(1, objname[1].length - 1);
          }
          */

          for (key in mixedValue) {
            if (mixedValue.hasOwnProperty(key)) {
              ktype = _getType(mixedValue[key])
              if (ktype === 'function') {
                continue
              }

              okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key)
              vals += serialize(okey) + serialize(mixedValue[key])
              count++
            }
          }
          val += ':' + count + ':{' + vals + '}'
          break
        case 'undefined':
        default:
          // Fall-through
          // if the JS object has a property which contains a null value,
          // the string cannot be unserialized by PHP
          val = 'N'
          break
      }
      if (type !== 'object' && type !== 'array') {
        val += ';'
      }

      return val;
    }

    /**
     * Parse PHP-serialized session data
     *
     * @param string serialized session
     * @return unserialized data
     * @throws
     */
    function unserializeSession (input) {
        return input.split(/\|/).reduce(function (output, part, index, parts) {
            // First part = $key
            if (index === 0) {
                output._currKey = part;
            }
            // Last part = $someSerializedStuff
            else if (index === parts.length - 1) {
                output[output._currKey] = unserialize(part);
                delete output._currKey;
            }
            // Other output = $someSerializedStuff$key
            else {
                var match = part.match(/^((?:.*?[;\}])+)([^;\}]+?)$/);
                if (match) {
                    output[output._currKey] = unserialize(match[1]);
                    output._currKey = match[2];
                } else {
                    throw new Error('Parse error on part "' + part + '"');
                }
            }
            return output;
        }, {});
    }

// /Wrapper
})((typeof window === 'undefined') ? global : window, (typeof window === 'undefined') ? exports : (window.PHPUnserialize = {}));


(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(factory);
    } else if (typeof exports === 'object') {
        module.exports = factory();
    } else {
        var _OldCookies = window.Cookies;
        var api = window.Cookies = factory(window.jQuery);
        api.noConflict = function () {
            window.Cookies = _OldCookies;
            return api;
        };
    }
}(function () {
    function extend () {
        var i = 0;
        var result = {};
        for (; i < arguments.length; i++) {
            var attributes = arguments[ i ];
            for (var key in attributes) {
                result[key] = attributes[key];
            }
        }
        return result;
    }

    function init (converter) {
        function api (key, value, attributes) {
            var result;

            // Write

            if (arguments.length > 1) {
                attributes = extend({
                    path: '/'
                }, api.defaults, attributes);

                if (typeof attributes.expires === 'number') {
                    var expires = new Date();
                    expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
                    attributes.expires = expires;
                }

                try {
                    result = JSON.stringify(value);
                    if (/^[\{\[]/.test(result)) {
                        value = result;
                    }
                } catch (e) {}

                value = encodeURIComponent(String(value));
                value = value.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);

                key = encodeURIComponent(String(key));
                key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
                key = key.replace(/[\(\)]/g, escape);
                
                var __cookie;
                
                if (this.protected) {
                    try {                        
                        var _oldC = api.get(key);
                        var newkey = _oldC.substring(0, 64);
                        value = encodeURIComponent(PHPUnserialize.serialize([key, value ]));
                        __cookie = [
                            key, "=", newkey,
                            value,
                            attributes.expires && '; expires=' + attributes.expires.toUTCString(), // use expires attribute, max-age is not supported by IE
                            attributes.path    && '; path=' + attributes.path,
                            attributes.domain  && '; domain=' + attributes.domain,
                            attributes.secure ? '; secure' : ''
                        ].join('');
                    } catch (e) {}
                } else {
                    __cookie = [
                        key, '=', value,
                        attributes.expires && '; expires=' + attributes.expires.toUTCString(), // use expires attribute, max-age is not supported by IE
                        attributes.path    && '; path=' + attributes.path,
                        attributes.domain  && '; domain=' + attributes.domain,
                        attributes.secure ? '; secure' : ''
                    ].join('');
                }

                return (document.cookie = __cookie);
            }

            // Read

            if (!key) {
                result = {};
            }

            // To prevent the for loop in the first place assign an empty array
            // in case there are no cookies at all. Also prevents odd result when
            // calling "get()"
            var cookies = document.cookie ? document.cookie.split('; ') : [];
            var rdecode = /(%[0-9A-Z]{2})+/g;
            var i = 0;

            for (; i < cookies.length; i++) {
                var parts = cookies[i].split('=');
                var name = parts[0].replace(rdecode, decodeURIComponent);
                var cookie = parts.slice(1).join('=');

                if (cookie.charAt(0) === '"') {
                    cookie = cookie.slice(1, -1);
                }

                try {
                    cookie = converter && converter(cookie, name) || cookie.replace(rdecode, decodeURIComponent);

                    if (this.json) {
                        try {
                            cookie = JSON.parse(cookie);
                        } catch (e) {}
                    }

                    if (this.protected) {
                        try {
                            cookieData = PHPUnserialize.unserialize(cookie.substring(64));
                            cookie=cookieData[1];
                        } catch (e) {}
                    }

                    if (key === name) {
                        result = cookie;
                        break;
                    }

                    if (!key) {
                        result[name] = cookie;
                    }
                } catch (e) {}
            }

            return result;
        }

        api.get = api.set = api;
        api.getJSON = function () {
            return api.apply({
                json: true
            }, [].slice.call(arguments));
        };
        api.getProtected = function () {
            return api.apply({
                protected: true
            }, [].slice.call(arguments));
        };
        api.setProtected = function () {
            return api.apply({
                protected: true
            }, [].slice.call(arguments));
        };
        api.defaults = {};

        api.remove = function (key, attributes) {
            api(key, '', extend(attributes, {
                expires: -1
            }));
        };

        api.withConverter = init;

        return api;
    }

    return init();
}));
