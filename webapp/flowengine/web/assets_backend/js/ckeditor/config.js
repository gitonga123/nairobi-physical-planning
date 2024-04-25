/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
	];

	config.filebrowserBrowseUrl = '/assets_backend/kcfinder/browse.php?opener=ckeditor&type=files';
	config.filebrowserImageBrowseUrl = '/assets_backend/kcfinder/browse.php?opener=ckeditor&type=images';
	config.filebrowserFlashBrowseUrl = '/assets_backend/kcfinder/browse.php?opener=ckeditor&type=flash';
	config.filebrowserUploadUrl = '/assets_backend/kcfinder/upload.php?opener=ckeditor&type=files';
	config.filebrowserImageUploadUrl = '/assets_backend/kcfinder/upload.php?opener=ckeditor&type=images';
	config.filebrowserFlashUploadUrl = '/assets_backend/kcfinder/upload.php?opener=ckeditor&type=flash';

	CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
    CKEDITOR.config.forcePasteAsPlainText = false; // default so content won't be manipulated on load
    CKEDITOR.config.basicEntities = true;
    CKEDITOR.config.entities = true;
    CKEDITOR.config.entities_latin = false;
    CKEDITOR.config.entities_greek = false;
    CKEDITOR.config.entities_processNumerical = false;
    CKEDITOR.config.fillEmptyBlocks = function (element) {
            return true; // DON'T DO ANYTHING!!!!!
    };

    CKEDITOR.config.allowedContent = true; // don't filter my data
};
