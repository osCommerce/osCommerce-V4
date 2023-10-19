tl.combineReducers = Redux.combineReducers(tl.reducers);

tl.store = Redux.createStore(tl.combineReducers, window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__())

tl.backState = tl.store.getState();

tl.allSubscribers = [];

tl.subscribe = function(key, callBack) {
    tl.allSubscribers.push({key: key, callBack: callBack})
}

tl.store.subscribe(function(){
    var state = tl.store.getState();

    tl.allSubscribers.forEach(function(subscribe){
        var key = subscribe.key;
        var callBack = subscribe.callBack;

        if (typeof key === 'string'){
            if (JSON.stringify(state[key]) !== JSON.stringify(tl.backState[key])) {
                callBack()
            }
        }
        if (Array.isArray(key)){
            if (isDifferentElements(key, state, tl.backState)) {
                callBack()
            }
        }
    })
    tl.backState = state;
});

function isDifferentElements(path, obj1, obj2){
    if (obj1===null && obj2===null) {
        return false
    }else if(obj1===null || obj2===null) {
        return true
    }
    if (path.length > 1) {
        if (typeof obj1[path[0]] === 'object' && typeof obj2[path[0]] === 'object') {

            return isDifferentElements(path.slice(1), obj1[path[0]], obj2[path[0]])

        } else if(
            typeof obj1[path[0]] === 'object' && isElementExist(path, obj1) ||
            typeof obj2[path[0]] === 'object' && isElementExist(path, obj2)
        ){

            return true
        }
    } else if (JSON.stringify(obj1[path[0]]) !== JSON.stringify(obj2[path[0]])) {
        return true
    }
    return false;
}

function isElementExist(path, obj){
    if (obj===null) return false;
    if (path.length > 1) {
        if (obj && typeof obj[path[0]] === 'object') {

            return isElementExist(path.slice(1), obj[path[0]])

        }
    } else if (obj[path[0]]) {
        return true
    }
    return false;
}

function setElementInObject(path, obj, value){
    var link = obj;
    path.slice(0, -1).forEach(function(element){
        if (!link[element]) link[element] = {};

        link = link[element];
    })
    link[path.pop()] = value
}

function setGetParam(href, paramName, paramValue){
    if (href == window.location.href) {
        const url = new URL(href);
        url.searchParams.set(paramName, paramValue);
        return url.toString();
    }
    var res = '';
    var d = href.split("#")[0].split("?");
    var base = d[0];
    var query = d[1];
    if(d[1]) {
        var params = query.split("&");
        for(var i = 0; i < params.length; i++) {
            var keyval = params[i].split("=");
            if(keyval[0] != paramName) {
                res += encodeURI($('<div>' + decodeURI(params[i]) + '</div>').text()) + '&';
            }
        }
    }
    if (paramValue) {
        res += paramName + '=' + encodeURI($('<div>' + decodeURI(paramValue) + '</div>').text());
    }
    return base + (res ? '?' : '') + res;
}

function getMainUrl() {
    var mainUrl = '';
    if ((typeof(entryData) === 'object') && (entryData !== null) && (typeof(entryData.mainUrl) === 'string')) {
        mainUrl = ('' + entryData.mainUrl).replace(/(\/|\\)+$/g, '');
    }
    return mainUrl;
}

function createJsUrl(file) {
    if (typeof entryData.jsPathUrl !== 'string') {
        return file;
    }
    var newUrl = entryData.jsPathUrl.replace(/(\/|\\)+$/g, '');
    if (!file) {
        return newUrl;
    }
    newUrl = newUrl + '/' + file + (entryData.themeVersion ? '?' + entryData.themeVersion : '' );
    return newUrl;
}