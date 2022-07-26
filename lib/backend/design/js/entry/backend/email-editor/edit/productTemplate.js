
import widgetMenuTemplate from "./widgetMenuTemplate";

export default function(data){
    let imgUrl;
    if (data.image) {
        imgUrl = document.location.origin + data.image
    } else {
        imgUrl = '../themes/basic/img/na.png'
    }

    let specialPrice = '';
    if (data.special_price) {
        specialPrice = `<div class="special-price">${data.special_price}</div>`;
    }

    data.value = data.value.replace('\\\'', '\'');

    return `
<div class="product-item box" data-id="${data.id}">
    <div class="image"><a href="${data.link}"><img src="${imgUrl}"></a></div>
    <div class="name"><a href="${data.link}">${data.title}</a></div>
    <div class="${data.special_price ? 'old-' : ''}price">${data.price}</div>
    ${specialPrice}
    ${widgetMenuTemplate()}
</div>
    `;
}