export default function(url, getParams){

    let newUrl = new URL(url);
    for (let name in getParams) {
        if (getParams[name] === '') {
            newUrl.searchParams.delete(name);
        } else {
            newUrl.searchParams.set(name, getParams[name]);
        }
    }

    return newUrl.href;
}