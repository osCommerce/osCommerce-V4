/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
  config.height = '300';
  config.width = '100%';
  config.allowedContent = true;

  var base_url = window.location.href.substr(0, window.location.href.lastIndexOf('/'));

  config.filebrowserBrowseUrl ='js/ckeditor/fm/browser/default/browser.html?Connector=' + base_url + '/js/ckeditor/fm/connectors/php/connector.php';
  config.filebrowserImageBrowseUrl = 'js/ckeditor/fm/browser/default/browser.html?Type=Image&Connector=' + base_url + '/js/ckeditor/fm/connectors/php/connector.php';
  config.filebrowserFlashBrowseUrl = 'js/ckeditor/fm/browser/default/browser.html?Type=Flash&Connector=' + base_url + '/js/ckeditor/fm/connectors/php/connector.php';
  config.filebrowserUploadUrl  = '' + base_url + '/js/ckeditor/fm/connectors/php/upload.php?Type=File';
  config.filebrowserImageUploadUrl = '' + base_url + '/js/ckeditor/fm/connectors/php/upload.php?Type=Image';
  config.filebrowserFlashUploadUrl = '' + base_url + '/js/ckeditor/fm/connectors/php/upload.php?Type=Flash';

};
