export default function () {
    return `
<div class="file-manager">
    <div>
    
        <div class="text-drop-files">${entryData?.tr?.TEXT_DROP_FILES} ${entryData?.tr?.TEXT_OR}</div>
        <div class="file-manager-buttons">
            <span class="btn btn-from-computer">${entryData?.tr?.UPLOAD_FROM_COMPUTER}</span>
            <span class="btn btn-from-gallery">${entryData?.tr?.UPLOAD_FROM_GALLERY}</span>
        </div>
        <div class="upload-progress">
            <div class="upload-progress-percent"></div>
            <div class="upload-progress-bar"><div class="upload-progress-bar-content"></div></div>
            <div class="upload-progress-val"></div>
        </div>
        <div class="uploaded-wrap">
        </div>
        
    </div>
</div>
    `;
}