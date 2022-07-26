{use class="frontend\design\Info"}
<link href='https://fonts.googleapis.com/css?family=Lato:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>
<link href='{Info::themeFile('/css/wrist.css')}' rel='stylesheet' type='text/css'>
<form name="wristband_settings" id="wristband_settings_form" method="post" onsubmit="return updateBoxParams();">
<div class="wrapp-wristband">
    <div class="wristband-title">
        Design your own wristbands
    </div>
    <div class="wristband-sub-title">
        Choose a version of wristband designer
    </div>
    <div class="wristband-all-tab">
        <ul class="ul-top">
            <li class="simple-wrist active"><span>Simple</span></li>
            <li class="adv-wrist"><span>Advanced</span></li>
        </ul>
        <div class="wrist-tab-1 wrist-tab-big">
            <ul>
                <li class="active"><span>Tyvek Paper Wristbands</span></li>
                <li class="vinil-pl"><span>Vinyl/Plastic wristbands</span></li>
            </ul>
            <div>
                <ul class="ul-top-div">
                    <li class="active"><span>Custom printed</span></li>
                    <li class="li-plain"><span>Plain</span></li>
                </ul>
                <div class="cus-print">
                    <div class="cus-print-wrist">
                        <div class="cus-print-wrist-cont">
                            <div class="wcm_print" id="wristband_settings_response">
                                <span class="wcm_logo wcm_logo-left" onclick="uploadLogoStart('left');">Add Logo /Artwork</span><span class="wcm_text"><span class="wcm_text-1" onclick="addTextBox(this);">Type text here or use the options below</span><span class="wcm_text-2" onclick="switchToTextTab();">Use the options below - use the comments box if you want a different type of layout</span></span><span class="wcm_logo wcm_logo-right" onclick="uploadLogoStart('right');">Add Logo /Artwork</span>
                            </div>
                            <div class="wcm_print2">
                                <div>
                                    Drag and drop text and artwork from below
                                </div>
                            </div>
                            <div class="wcm_print3">
                                <div>
                                    &nbsp;
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="show-pr-area line-add-text">
                        <i>Show printable area?</i>
                        <input name="show-printable-area" type="checkbox" class="check-on-off" checked="checked" />
                    </div>
                </div>
            </div>
        </div>
        <div class="wrist-tab-2 wrist-tab-small">
            <ul>
                <li><span>Size/Colour</span></li>
                <li class="check-simple check-plain" id="text_tab"><span>Text</span></li>
                <li class="check-simple check-plain" id="logos_tab"><span>Logos/Artwork</span></li>
                <li class="check-advanced check-plain"><span>Text/Logos/Artwork</span></li>
                <li class="vinil-barcodes-li"><span>Barcodes, QR Codes, Numbers</span></li>
                <li><span>UK Wristband branding</span></li>
            </ul>
            <div>
                <div>
                    <div class="line-size">
                        <label class="lab-opti">Size:</label>
                        <div class="size-choose size-choose-1">3/4"(19 mm)</div>
                        <div class="size-choose size-choose-2 choose-active">1"(25 mm)</div>
                    </div>
                    <div class="line-colour">
                        <label class="lab-opti">Colour:</label>
                        <div class="lc-opti lc-opti-1" data-colour="1"><i></i><span>White</span></div>
                        <div class="lc-opti lc-opti-2" data-colour="2"><i></i><span>Purple</span></div>
                        <div class="lc-opti lc-opti-3" data-colour="3"><i></i><span>Neon Pink</span></div>
                        <div class="lc-opti lc-opti-4" data-colour="4"><i></i><span>Red</span></div>
                        <div class="lc-opti lc-opti-5" data-colour="5"><i></i><span>Neon Orange</span></div>
                        <div class="lc-opti lc-opti-6 active" data-colour="6"><i></i><span>Yellow</span></div>
                        <div class="lc-opti lc-opti-7" data-colour="7"><i></i><span>Neon Yellow</span></div>
                        <div class="lc-opti lc-opti-8" data-colour="8"><i></i><span>Aqua</span></div>
                        <div class="lc-opti lc-opti-9" data-colour="9"><i></i><span>Neon Green</span></div>
                        <div class="lc-opti lc-opti-10" data-colour="10"><i></i><span>Dark Green</span></div>
                        <div class="lc-opti lc-opti-11" data-colour="11"><i></i><span>Blue</span></div>
                        <div class="lc-opti lc-opti-12" data-colour="12"><i></i><span>Sky Blue</span></div>
                        <div class="lc-opti lc-opti-13" data-colour="13"><i></i><span>Gold</span></div>
                        <div class="lc-opti lc-opti-14" data-colour="14"><i></i><span>Silver</span></div>
                    </div>
                    <div class="line-colour">
                        <label class="lab-opti">&nbsp;</label>
                        <div class="lc-opti lc-opti-cus" data-colour="0"><i></i><span>Mixed colours</span></div>
                    </div>
                </div>
                <div class="check-simple-div check-plain-div">
                    <div class="line-border-bottom line-center choose-text">
                        <div class="ch_opti choose-active choose-text-opti-1">
                            Text
                        </div>
                        <div class="ch_opti choose-text-opti-2">
                            No text just logos/artwork
                        </div>
                    </div>
                    <div id="use_text">
                        <div class="utwrapp line-border-bottom">
                            <div class="line-add-text line-add-text-style">
                                <select name="text_font_1" onchange="updatePreviewBox();">
                                    <option value="impact">Font</option>
                                    <option value="impact">Impact</option>
                                    <option value="arial">Arial</option>
                                </select>
                                <select name="text_size_1" onchange="updatePreviewBox();">
                                    <option value="20">Size</option>
                                    <option value="10">10</option>
                                    <option value="12">12</option>
                                    <option value="14">14</option>
                                    <option value="16">16</option>
                                    <option value="18">18</option>
                                    <option value="20">20</option>
                                    <option value="22">22</option>
                                    <option value="24">24</option>
                                </select>
                            </div>
                            <div>
                                <label class="lab-opti lab-opti-top">Text Line 1:</label>
                                <textarea rows="2" cols="82" name="text_line_1" class="form-control" onchange="updatePreviewBox();"></textarea>
                            </div>
                        </div>
                        <div class="line-add-text" style="margin-bottom: 15px;">
                            <label class="lab-opti">Add a second line of text?</label><input name="use-second-line" value="yes" type="checkbox" class="check-on-off" />
                        </div>
                        <div style="display: none;" id="text_line_2" class="utwrapp">
                            <div class="line-add-text line-add-text-style">
                                <select name="text_font_2" onchange="updatePreviewBox();">
                                    <option value="arial">Font</option>
                                    <option value="impact">Impact</option>
                                    <option value="arial">Arial</option>
                                </select>
                                <select name="text_size_2" onchange="updatePreviewBox();">
                                    <option value="10">Size</option>
                                    <option value="10">10</option>
                                    <option value="12">12</option>
                                    <option value="14">14</option>
                                    <option value="16">16</option>
                                    <option value="18">18</option>
                                    <option value="20">20</option>
                                    <option value="22">22</option>
                                    <option value="24">24</option>
                                </select>
                            </div>
                            <div>
                                <label class="lab-opti lab-opti-top">Text Line 2:</label>
                                <textarea rows="2" cols="82" name="text_line_2" class="form-control" onchange="updatePreviewBox();"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="check-simple-div check-plain-div">
                    <div class="choose-logo">
                        <ul class="line-border-bottom line-center">
                            <li class="ch_opti choose-active choose-logo-opti-1">Choose artwork</li>
                            <li class="ch_opti choose-logo-opti-2">Upload logo</li>
                            <li  class="ch_opti choose-logo-opti-3">Will email logo / artwork</li>
                        </ul>
                        <div>
                            <div>
                                <div class="line-border-bottom choose-position">
                                    <label class="lab-opti">Position:</label>
                                    <select name="select-position" class="wrist-select" onchange="updatePreviewBox();">
                                        <option value="left">Left of the Text</option>
                                        <option value="right">Right of the Text</option>
                                        <option value="both">Both Sides</option>
                                    </select>
                                </div>
                                <div class="choose-logo-line">
                                    <span onclick="setArt('wlogo-2.png')"><img src="{$app->request->baseUrl}/images/wlogo-2.png" /></span>
                                    <span onclick="setArt('wlogo-3.png')"><img src="{$app->request->baseUrl}/images/wlogo-3.png" /></span>
                                    <span onclick="setArt('wlogo-4.png')"><img src="{$app->request->baseUrl}/images/wlogo-4.png" /></span>
                                    <span onclick="setArt('wlogo-5.png')"><img src="{$app->request->baseUrl}/images/wlogo-5.png" /></span>
                                    <span onclick="setArt('wlogo-6.png')"><img src="{$app->request->baseUrl}/images/wlogo-6.png" /></span>
                                    <span onclick="setArt('wlogo-7.png')"><img src="{$app->request->baseUrl}/images/wlogo-7.png" /></span>
                                    <span onclick="setArt('wlogo-8.png')"><img src="{$app->request->baseUrl}/images/wlogo-8.png" /></span>
                                    <span onclick="setArt('wlogo-9.png')"><img src="{$app->request->baseUrl}/images/wlogo-9.png" /></span>
                                    <span onclick="setArt('wlogo-10.png')"><img src="{$app->request->baseUrl}/images/wlogo-10.png" /></span>
                                    <span onclick="setArt('wlogo-11.png')"><img src="{$app->request->baseUrl}/images/wlogo-11.png" /></span>
                                    <span onclick="setArt('wlogo-12.png')"><img src="{$app->request->baseUrl}/images/wlogo-12.png" /></span>
                                    <span onclick="setArt('wlogo-13.png')"><img src="{$app->request->baseUrl}/images/wlogo-13.png" /></span>
                                    <span onclick="setArt('wlogo-14.png')"><img src="{$app->request->baseUrl}/images/wlogo-14.png" /></span>
                                    <span onclick="setArt('wlogo-15.png')"><img src="{$app->request->baseUrl}/images/wlogo-15.png" /></span>
                                    <span onclick="setArt('wlogo-16.png')"><img src="{$app->request->baseUrl}/images/wlogo-16.png" /></span>
                                    <span onclick="setArt('wlogo-17.png')"><img src="{$app->request->baseUrl}/images/wlogo-17.png" /></span>
                                    <span onclick="setArt('wlogo-18.png')"><img src="{$app->request->baseUrl}/images/wlogo-18.png" /></span>
                                    <span onclick="setArt('wlogo-19.png')"><img src="{$app->request->baseUrl}/images/wlogo-19.png" /></span>
                                </div>
                            </div>
                            <div>
                                <div class="line-border-bottom wcol-2">
                                    <div>
                                        <label class="lab-opti">File upload:</label>
                                        <span class="up-logo-btn"><input type="file" name="up-logo" id="file-select" onchange="uploadLogo();" /></span>
                                        <i class="info-text info-text-pad">Requirement: JPEG, GIF, PNG, 2 MB max</i>
                                    </div>
                                    <div>
                                        <label class="lab-opti">Position:</label>
                                        <select name="select-position-upload" class="wrist-select" onchange="updatePreviewBox();">
                                            <option value="left">Left of the Text</option>
                                            <option value="right">Right of the Text</option>
                                            <option value="both">Both Sides</option>
                                        </select>
                                        <i class="info-text info-text-pad">For more logo options switch to the advanced designer</i>
                                    </div>
                                </div>
                                <div class="wcol-2 div-up-lo">
                                    <span>Image quality guidance</span>
                                    <div>
                                        • Only white wristbands will show true colour print.<br>
                                        • Colour print may be effected by the wristbands background  colour<br>
                                        • All white parts in artwork will show the background wristband colour.
                                    </div>
                                    <div>• All white parts in artwork will show the background wristband colour.</div>
                                </div>
                            </div>
                            <div>
                                <span style="color: #fd6241; font-size: 18px;">Please email your artwork to sales@ukwristbands.com</span>
                            </div>
                        </div>
                    </div>  
                </div>
                <div class="check-advanced-div check-plain-div">
                    <div class="adv-text adv-text-1">
                        Text Line 1
                    </div>
                    <div class="adv-text adv-text-2">
                        Text Line 2
                    </div>
                    <div class="choose-logo">
                        <ul class="line-border-bottom line-center">
                            <li class="ch_opti choose-active choose-logo-opti-1">Choose artwork</li>
                            <li class="ch_opti choose-logo-opti-4">Upload logo</li>
                            <li  class="ch_opti choose-logo-opti-5">Will email logo / artwork</li>
                        </ul>
                        <div>
                            <div>                                
                                <div class="choose-logo-line">
                                    <span><img src="{$app->request->baseUrl}/images/wlogo-2.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-3.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-4.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-5.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-6.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-7.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-8.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-9.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-10.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-11.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-12.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-13.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-14.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-15.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-16.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-17.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-18.png" /></span><span><img src="{$app->request->baseUrl}/images/wlogo-19.png" /></span>
                                </div>
                            </div>
                            <div>
                                <div class="line-border-bottom wcol-2">
                                    <label class="lab-opti">File upload:</label>
                                        <span class="up-logo-btn"><input type="file" name="up-logo" /></span>
                                        <i class="info-text info-text-pad">Requirement: JPEG, GIF, PNG, 2 MB max</i>
                                </div>
                                <div class="wcol-2 div-up-lo">
                                    <span>Image quality guidance</span>
                                    <div>
                                        • Only white wristbands will show true colour print.<br>
                                        • Colour print may be effected by the wristbands background  colour<br>
                                        • All white parts in artwork will show the background wristband colour.
                                    </div>
                                    <div>• All white parts in artwork will show the background wristband colour.</div>
                                </div>
                            </div>
                            <div>
                                <span style="color: #fd6241; font-size: 18px;">Please email your artwork to sales@ukwristbands.com</span>
                            </div>
                        </div>
                    </div>  
                </div>
                <div class="vinil-barcodes-div">
                    <div class="line-add-barcode">
                        <label class="lab-opti">Add:</label>
                        <div class="add-barcode add-barcode-1 choose-active">No</div>
                        <div class="add-barcode add-barcode-2">Barcodes + &pound;36</div>
                        <div class="add-barcode add-barcode-3">QR Codes + &pound;36</div>
                        <div class="add-barcode add-barcode-4">Numbering + &pound;36</div>
                    </div>
                </div>
                <div class="uk-wrist-brand line-add-text">
                    <span>All of our wristbands contain our branding on the reverse side – this cannot be seen when the wristband is worn.</span>
                    <label class="lab-opti">Remove UK Wristbands branding from wristbands?</label><input name="remove-branding" value="yes" type="checkbox" class="check-on-off" checked="checked" />
                </div>
            </div>
        </div>
    </div>
</div>
<div id="wrist_popup">
    <div class="wr-pop-head">
        Mixed colours
        <a href="javascript:PopUpHide()"></a>
    </div>
    <div class="wr-pop-sub-head">This option allow you to to order several different coloured wristbands in one lot.  Additional cost + £4.00</div>
    <div class="wr-pop-con">
        <label>Select colours:</label>
        <div class="pop-col-wrp">
            <div>
                <div class="pline-color pline-color-1">
                    <div class="pline-color-name pline-color-name-1">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>White</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-2">
                    <div class="pline-color-name pline-color-name-2">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Purple</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-3">
                    <div class="pline-color-name pline-color-name-3">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Neon Pink</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-4">
                    <div class="pline-color-name pline-color-name-4">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Red</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-5">
                    <div class="pline-color-name pline-color-name-5">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Neon Orange</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-6">
                    <div class="pline-color-name pline-color-name-6">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Yellow</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-7">
                    <div class="pline-color-name pline-color-name-7">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Neon Yellow</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
            </div>
            <div>
                <div class="pline-color pline-color-8">
                    <div class="pline-color-name pline-color-name-8">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Aqua</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-9">
                    <div class="pline-color-name pline-color-name-9">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Neon Green</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-10">
                    <div class="pline-color-name pline-color-name-10">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Dark Green</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-11">
                    <div class="pline-color-name pline-color-name-11">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Blue</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-12">
                    <div class="pline-color-name pline-color-name-12">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Sky Blue</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-13">
                    <div class="pline-color-name pline-color-name-13">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Gold</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
                <div class="pline-color pline-color-14">
                    <div class="pline-color-name pline-color-name-14">
                        <span class="ch_pl-colour"></span><i></i>
                        <b>Silver</b>
                    </div>
                    <div class="pline-color-qty">
                        <span class="wrst-min"></span><input type="text" value="" /><span class="wrst-max"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="wr-pop-btn">
        <div><a href="javascript:PopUpHide()" class="wrist-btn">Cancel</a></div>
        <div><span class="wrist-btn wrist-btn-blue">Submit</span></div>
    </div>
</div>

    <input type="hidden" name="version" value="simple">
    <input type="hidden" name="material" value="paper">
    <input type="hidden" name="content" value="custom">
    <input type="hidden" name="size" value="25">
    <input type="hidden" name="colour" value="6">
    
    <input type="hidden" name="use-text" value="yes">
    <input type="hidden" name="use_second_line" value="no">
    
    <input type="hidden" name="use_logo" value="artwork">
    <input type="hidden" name="art_filename" value="">
    <input type="hidden" name="upload_filename" value="">
</form>
<div id="wristband_settings_response2"></div>
<script>
function updateBoxParams()
{
    var text_box = $('input[name="text_box"]').val();
    if (text_box != undefined) {
        $('textarea[name="text_line_1"]').text(text_box);
    }
    updatePreviewBox();
    return false;
}
function updatePreviewBox()
{
    $.post("{Yii::$app->urlManager->createUrl('wristband/params')}", $('#wristband_settings_form').serialize(), function(data, status){
        if (status == "success") {
            $('#wristband_settings_response').html(data);
        }
    },"html");
}
function setArt(name)
{
    $('input[name="art_filename"]').val(name);
    updatePreviewBox();
}

function uploadLogo()
{
    //console.log($('#file-select').val());
    
    var fileSelect = document.getElementById('file-select');
    var files = fileSelect.files;
    
    // Create a new FormData object.
    var formData = new FormData();
    // Loop through each of the selected files.
    //for (var i = 0; i < files.length; i++) {
      var file = files[0];

      // Check the file type.
      if (!file.type.match('image.*')) {
        //alert('Wrong image format!');
        $('input[name="upload_filename"]').val();
        return false;
      }

      // Add the file to the request.
      formData.append('logos', file, file.name);
    //}
    
    // Files
    //formData.append(name, file, filename);

    // Blobs
    //formData.append(name, blob, filename);

    // Strings
    //formData.append(name, value);   
    
    // Set up the request.
    var xhr = new XMLHttpRequest();

    // Open the connection.
    xhr.open('POST', '{Yii::$app->urlManager->createUrl('wristband/upload')}', true);
    
    // Set up a handler for when the request finishes.
    xhr.onload = function () {
      if (xhr.status === 200) {
        // File(s) uploaded.
        $('input[name="upload_filename"]').val(file.name);
        updatePreviewBox();
      } else {
        //alert('An error occurred!');
        $('input[name="upload_filename"]').val();
      }
    };

    // Send the Data.
    xhr.send(formData);

}

var textBoxActive = 0;
function addTextBox(obj)
{
    if (textBoxActive == 1) {
        return false;
    }
    $(obj).html('<input type="text" name="text_box">');
    textBoxActive = 1;
}
function switchToTextTab()
{
    $('#text_tab').click();
}

function switchToLogoTab()
{
    $('#logos_tab').click();
}
function uploadLogoStart(pos)
{
    $('#logos_tab').click();
    $('.choose-logo-opti-2').click();
    $('select[name="select-position-upload"]').val(pos);
    $('#file-select').click();
}

tl([
  '{Info::themeFile('/js/bootstrap-switch.js')}',
  '{Info::themeFile('/js/ckeditor/ckeditor.js')}'
], function(){
  $('.wristband-all-tab > ul li').click(function(){
    $('.wristband-all-tab > ul li').removeClass('active');
    $(this).addClass('active');
    if($('.wristband-all-tab > ul li.simple-wrist').hasClass('active')){
      $('.cus-print').removeClass('cus-print-adv');
      $('.wrist-tab-2').removeClass('wrist-tab-2-adv');
      $('input[name="version"]').val('simple');
    }else{
      $('.cus-print').addClass('cus-print-adv');
      $('.wrist-tab-2').addClass('wrist-tab-2-adv');
      $('input[name="version"]').val('advanced');
    }
    updatePreviewBox();
  });
  $('ul.ul-top-div li').click(function(){
    $('ul.ul-top-div li').removeClass('active');
    $(this).addClass('active');
    if($('ul.ul-top-div li.li-plain').hasClass('active')){
      $('.wristband-all-tab').addClass('w_check-plain');
      $('input[name="content"]').val('plain');
    }else{
      $('.wristband-all-tab').removeClass('w_check-plain');
      $('input[name="content"]').val('custom');
    }
    updatePreviewBox();
  });
  $('.wrist-tab-1 > ul > li').click(function(){
    $('.wrist-tab-1 > ul > li').removeClass('active');
    $(this).addClass('active');
    if($('.wrist-tab-1 > ul > li.vinil-pl').hasClass('active')){
      $('.wristband-all-tab').addClass('w_vinil-pl');
      $('input[name="material"]').val('vinil');
    }else{
      $('.wristband-all-tab').removeClass('w_vinil-pl');
      $('input[name="material"]').val('paper');
    }
    updatePreviewBox();
  });
  $('.line-size .size-choose').click(function(){
    $('.line-size .size-choose').removeClass('choose-active');
    $(this).addClass('choose-active');
    $('.cus-print').toggleClass('cus-print-small');
    if ($(this).hasClass('size-choose-1') ) {
      $('input[name="size"]').val('19');
    } else {
      $('input[name="size"]').val('25');
    }
    updatePreviewBox();
  });
  $('.add-barcode').click(function(){
    $('.add-barcode').removeClass('choose-active');
    $(this).addClass('choose-active');
  });
  $('.choose-text > div.ch_opti').click(function(){
    $('.choose-text > div.ch_opti').removeClass('choose-active');
    $(this).addClass('choose-active');

    if($(this).hasClass('choose-text-opti-1')){
      $('input[name="use-text"]').val('yes');
      $('#use_text').show();
    }else{
      $('input[name="use-text"]').val('no');
      $('#use_text').hide();
      switchToLogoTab();
    }
    updatePreviewBox();
  });
  $('.choose-logo li.ch_opti').click(function(){
    $('.choose-logo li.ch_opti').removeClass('choose-active');
    $(this).addClass('choose-active');

    if($(this).hasClass('choose-logo-opti-1')) {
      $('input[name="use_logo"]').val('artwork');
    } else if($(this).hasClass('choose-logo-opti-2')) {
      $('input[name="use_logo"]').val('upload');
    } else {
      $('input[name="use_logo"]').val('no');
    }
    updatePreviewBox();
  });
  $('.ch_pl-colour').click(function(){
    $(this).toggleClass('choose-active');
    $(this).parents('.pline-color').toggleClass('active-q');
  });
  $('.line-colour .lc-opti').click(function(){
    $('.line-colour .lc-opti').removeClass('active');
    $(this).addClass('active');
    var bgwrist = $(this).data('colour');
    $('input[name="colour"]').val(bgwrist);
    $('.cus-print-wrist').removeAttr('class').addClass('cus-print-wrist cus-print-wrist-' + bgwrist);
  });
  jQuery.fn.lightTabs = function(options){
    var createTabs = function(){
      tabs = this;
      i = 0;

      showPage = function(tabs, i){
        $(tabs).children("div").children("div").hide();
        $(tabs).children("div").children("div").eq(i).show();
        $(tabs).children("ul").children("li").removeClass("active");
        $(tabs).children("ul").children("li").eq(i).addClass("active");
      }

      showPage(tabs, 0);

      $(tabs).children("ul").children("li").each(function(index, element){
        $(element).attr("data-page", i);
        i++;
      });

      $(tabs).children("ul").children("li").click(function(){
        showPage($(this).parent().parent(), parseInt($(this).attr("data-page")));
      });
    };
    return this.each(createTabs);
  };
  //$('.wrist-tab-1').lightTabs();
  $('.wrist-tab-2').lightTabs();
  $('.choose-logo').lightTabs();
  {\frontend\design\Info::addBoxToCss('switch')}
  $('.check-on-off').bootstrapSwitch({
    offText: 'NO',
    onText: 'YES',
    onSwitchChange: function (element, arguments) {
      $(this).closest('form').trigger('cart-change');
      if (element.target.name == 'use-second-line') {
        if (arguments == true) {
          $('#text_line_2').show();
          $('input[name="use_second_line"]').val('yes');
        } else {
          $('#text_line_2').hide();
          $('input[name="use_second_line"]').val('no');
        }
        updatePreviewBox();
      }
    }
  });

  /*CKEDITOR.replace( 'text-line-1',
   {
   toolbar :
   [
   { name: 'styles', items : [ 'Font','FontSize' ] }
   ]
   });*/
  /*CKEDITOR.replace( 'text-line-2',
   {
   toolbar :
   [
   { name: 'styles', items : [ 'Font','FontSize' ] }
   ]
   });*/
  PopUpHide();
  $('.lc-opti-cus').click(function(){
    $("#wrist_popup").show();
    $('body').after('<div class="bg-pop-wrist"></div>');
    $('.bg-pop-wrist').css('height', $('body').height());
    $("#wrist_popup").height();
    var wrist_pop_top = ($('body').height() - $("#wrist_popup").height() + 200)/2;
    $("#wrist_popup").css('top', wrist_pop_top);
    var wrist_pop_left = ($('body').width() - $("#wrist_popup").width())/2;
    $("#wrist_popup").css('left', wrist_pop_left);
  });
  $('.wrst-max').click(function(){
    val = $(this).prev('input').attr('value');
    if (val < 100000){
      val++;
    }
    var input = $(this).prev('input');
    input.attr('value', val);
  });
  $('.wrst-min').click(function(){
    val = $(this).next('input').attr('value');
    if (val > 1){
      val--;
    }
    var input = $(this).next('input');
    input.attr('value', val);
  });
});

    function PopUpShow(){
        $("#wrist_popup").show();
    }
    function PopUpHide(){
        $("#wrist_popup").hide();
        $('.bg-pop-wrist').remove();
    }
</script>