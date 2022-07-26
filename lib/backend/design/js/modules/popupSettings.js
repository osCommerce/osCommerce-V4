


export default function(popupName, val = null){
    const popupSettingsString = localStorage.getItem('popupSettings')

    let popupSettings = {};
    if (popupSettingsString) {
        popupSettings = JSON.parse(popupSettingsString);
        if (!popupSettings) {
            popupSettings = {};
        }
    }

    if (val === null) {
        if (popupSettings[popupName]) {
            return popupSettings[popupName]
        } else {
            return null;
        }
    }

    if (!popupSettings[popupName]) {
        popupSettings[popupName] = {}
    }

    popupSettings[popupName] = $.extend(true, popupSettings[popupName], val);

    localStorage.setItem('popupSettings', JSON.stringify(popupSettings))
}