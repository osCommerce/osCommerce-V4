
export default function (property, defaultVal = null) {
    let entryData = window.entryData;

    if (Array.isArray(property) && property.length > 0 && entryData && entryData[property[0]]) {

        let obj = entryData;
        property.forEach( p => obj =  obj && obj[p] ? obj[p] : defaultVal );
        return obj;

    } else if ( typeof property === 'number' || typeof property === 'string' ) {

        return entryData && entryData[property] ? entryData[property] : defaultVal

    } else {

        return defaultVal

    }
}