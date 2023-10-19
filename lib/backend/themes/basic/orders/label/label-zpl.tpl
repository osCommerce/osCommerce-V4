<html>
<head>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/BrowserPrint-2.0.0.75.min.js"></script>
<script type="text/javascript">
var selected_device;
var devices = [];
function setup()
{
    //Get the default device from the application as a first step. Discovery takes longer to complete.
    BrowserPrint.getDefaultDevice("printer", function(device)
        {

            //Add device to list of devices and to html select element
            selected_device = device;
            devices.push(device);
            var html_select = document.getElementById("selected_device");
            var option = document.createElement("option");
            option.text = device.name;
            html_select.add(option);

            //Discover any other devices available to the application
            BrowserPrint.getLocalDevices(function(device_list){
                for(var i = 0; i < device_list.length; i++)
                {
                    //Add device to list of devices and to html select element
                    var device = device_list[i];
                    if(!selected_device || device.uid != selected_device.uid)
                    {
                        devices.push(device);
                        var option = document.createElement("option");
                        option.text = device.name;
                        option.value = device.uid;
                        html_select.add(option);
                    }
                }

            }, function(){ alert("Error getting local devices") },"printer");

        }, function(error){
            alert(error);
        })
}
function getConfig(){
    BrowserPrint.getApplicationConfiguration(function(config){
        alert(JSON.stringify(config))
    }, function(error){
        alert(JSON.stringify(new BrowserPrint.ApplicationConfiguration()));
    })
}
function writeToSelectedPrinter(dataToWrite)
{
    selected_device.send(dataToWrite, undefined, errorCallback);
}
var readCallback = function(readData) {
    if(readData === undefined || readData === null || readData === "")
    {
        alert("No Response from Device");
    }
    else
    {
        alert(readData);
    }
}
var errorCallback = function(errorMessage){
    alert("Error: " + errorMessage);
}
function readFromSelectedPrinter()
{
    selected_device.read(readCallback, errorCallback);
}
function getDeviceCallback(deviceList)
{
    alert("Devices: \n" + JSON.stringify(deviceList, null, 4))
}

function sendImage(imageUrl)
{
    url = window.location.href.substring(0, window.location.href.lastIndexOf("/"));
    url = url + "/" + imageUrl;
    selected_device.sendUrl(url, undefined, errorCallback)
}
function sendImageHttp(imageUrl)
{
    url = window.location.href.substring(0, window.location.href.lastIndexOf("/"));
    url = url + "/" + imageUrl;
    url = url.replace("https", "http");
    selected_device.sendUrl(url, undefined, errorCallback)
}
function onDeviceSelected(selected)
{
    for(var i = 0; i < devices.length; ++i){
        if(selected.value == devices[i].uid)
        {
            selected_device = devices[i];
            return;
        }
    }
}
//window.onload = setup;
var zpl_text = `{$parcel_label}`;
</script>
</head>
<body>

<div class="popup-heading">{$smarty.const.TEXT_PRINT_LABEL}</div>
<div class="popup-content">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font">
    <tr>
        <td>
Selected Device: <select id="selected_device" class="form-select" onchange="onDeviceSelected(this)"></select> <!-- <input type="button" value="Change" onclick="changeDevice();"> -->
<br/><br/> 
<input type="button" value="{$smarty.const.TEXT_PRINT_LABEL}" class="btn btn-primary" onclick="writeToSelectedPrinter(zpl_text)">&nbsp;&nbsp; {$smarty.const.TEXT_OR} &nbsp;&nbsp;
<a href="{\Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $orders_id, 'orders_label_id' => $orders_label_id])}" class="btn">{$smarty.const.IMAGE_DOWNLOAD}</a><br/><br/>
        </td>
    </tr>
</table>
</div>
<script type="text/javascript">
setup();
</script>

</body>
</html>
