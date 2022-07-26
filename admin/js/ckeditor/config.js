/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
  config.height = '400';
  config.width = '800';
 
  var base_url = window.location.href.substr(0, window.location.href.lastIndexOf('/'));
  base_url = base_url.substr(0, base_url.lastIndexOf('/'));
  if (entryData && entryData.mainUrl) {
      base_url = entryData.mainUrl
  }
	
  config.filebrowserBrowseUrl =base_url+'/js/ckeditor/fm/browser/default/browser.html?Type=Image&Connector=' + base_url + '/js/ckeditor/fm/connectors/php/connector.php';
  config.filebrowserImageBrowseUrl = base_url+'/js/ckeditor/fm/browser/default/browser.html?Type=Image&Connector=' + base_url + '/js/ckeditor/fm/connectors/php/connector.php';
  config.filebrowserFlashBrowseUrl = base_url+'/js/ckeditor/fm/browser/default/browser.html?Type=Flash&Connector=' + base_url + '/js/ckeditor/fm/connectors/php/connector.php';
  config.filebrowserUploadUrl  = '' + base_url + '/js/ckeditor/fm/connectors/php/upload.php?Type=File';
  config.filebrowserImageUploadUrl = '' + base_url + '/js/ckeditor/fm/connectors/php/upload.php?Type=Image';
  config.filebrowserFlashUploadUrl = '' + base_url + '/js/ckeditor/fm/connectors/php/upload.php?Type=Flash';
  config.allowedContent = true;

  config.autoParagraph = false
};