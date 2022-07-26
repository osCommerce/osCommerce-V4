let wrapper = function(content, buttons = [], heading = false){

    let allContent = '';
    if (heading) {
        allContent += `<div class="popup-heading">${heading}</div>`
    }
    allContent += `<div class="popup-content pop-mess-cont">${content}</div>`;

    if (buttons.length > 0) {
        allContent += `<div class="popup-buttons">`;
        buttons.forEach(function(item, i) {
            allContent += `<span class="btn ${item.class}">${item.name}</span>`;
        });
        allContent += `</div>`;
    }

    return allContent;
};
export default wrapper;