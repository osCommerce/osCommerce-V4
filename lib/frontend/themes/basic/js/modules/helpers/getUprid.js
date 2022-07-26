if (!helpers) var helpers = {};
helpers.getUprid = function($productId, attributes){
    if (!$productId) return false;

    var uprid = $productId;

    if(typeof attributes === "object") {
        var newAttributes = {};
        var attributeIds = [];


        for (var attributeId in attributes){
            if (!attributes.hasOwnProperty(attributeId)) continue;

            var optionId = attributes[attributeId];

            if (attributeId.search('[a-z]') !== -1){
                attributeId = attributeId.match('[0-9]+')[0];
            }
            attributeIds.push(+attributeId)

            newAttributes[attributeId] = optionId;
        }

        attributeIds = attributeIds.sort(function (a, b) { return a - b});

        var attrId = 0;
        var key = 0;
        for (key in attributeIds) {
            attrId = attributeIds[key];

            uprid = uprid + '{' + attrId + '}' + newAttributes[attrId]
        }
    }

    return uprid;
}