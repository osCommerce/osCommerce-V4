export default async function (resource, action, sendData = {}, settings = {}) {

    const url = new URL(entryData.mainUrl + "/design");

    url.searchParams.set('resource', resource);
    url.searchParams.set('action', action);

    if (!settings.method || settings.method.toLowerCase() === 'get') {
        settings.method = 'GET';
        for (let param in sendData) {
            url.searchParams.set(param, sendData.param);
        }
    }

    if (settings.method.toLowerCase() === 'post') {
        settings.body = JSON.stringify(sendData);
        if (!settings.headers) {
            settings.headers = {
                'Content-Type': 'application/json;charset=utf-8'
            }
        }
    }

    const response = await fetch(url, settings);



    /*const response = await fetch(url, {
        method: 'POST', // *GET, POST, PUT, DELETE, etc.
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
            'Content-Type': 'application/json'
            // 'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer', // no-referrer, *client
        body: JSON.stringify(sendData) // body data type must match "Content-Type" header
    });*/

    return await response.json(); // parses JSON response into native JavaScript objects
}