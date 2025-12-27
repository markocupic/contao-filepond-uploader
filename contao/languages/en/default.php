<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

$GLOBALS['TL_LANG']['ERR']['fileminwidth'] = 'File %s must have a minimum width of %d pixels!';
$GLOBALS['TL_LANG']['ERR']['fileminheight'] = 'File %s must have a minimum height of %d pixels!';
$GLOBALS['TL_LANG']['ERR']['filepond.error'] = 'An unknown error occurred while trying to upload the file.';

// Filepond
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelIdle'] = 'Drag & Drop your files or <span class=\"filepond--label-action\">Browse</span>';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelInvalidField'] = 'Field contains invalid files';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileWaitingForSize'] = 'Waiting for size';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileSizeNotAvailable'] = 'Size not available';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileLoading'] = 'Loading';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileLoadError'] = 'Error during load';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessing'] = 'Uploading';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingComplete'] = 'Upload complete';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingAborted'] = 'Upload cancelled';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingError'] = 'Error during upload';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingRevertError'] = 'Error during revert';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileRemoveError'] = 'Error during remove';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelTapToCancel'] = 'tap to cancel';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelTapToRetry'] = 'tap to retry';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelTapToUndo'] = 'tap to undo';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonRemoveItem'] = 'Remove';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonAbortItemLoad'] = 'Abort';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonRetryItemLoad'] = 'Retry';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonAbortItemProcessing'] = 'Cancel';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonUndoItemProcessing'] = 'Undo';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonRetryItemProcessing'] = 'Retry';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonProcessItem'] = 'Upload';

// Filepond Plugin: File validate size
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxFileSizeExceeded'] = 'File is too large';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxFileSize'] = 'Maximum file size is {filesize}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxTotalFileSizeExceeded'] = 'Maximum total size exceeded';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxTotalFileSize'] = 'Maximum total file size is {filesize}';

//// Filepond Plugin: File validate type
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileTypeNotAllowed'] = 'File of invalid type';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.fileValidateTypeLabelExpectedTypes'] = 'Expects {allButLastType} or {lastType}';

// Filepond Plugin: Image edit
// No translations available

// Filepond Plugin: Image exif orientation
// No translations available

// Filepond Plugin: Image preview
// No translations available

// Filepond Plugin: Image resize
// No translations available

// Filepond Plugin: Image transform
// No translations available

// Filepond Plugin: Image validate size
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelFormatError'] = 'Image type not supported';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageSizeTooSmall'] = 'Image is too small';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageSizeTooBig'] = 'Image is too big';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMinSize'] = 'Minimum size is {minWidth} × {minHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMaxSize'] = 'Maximum size is {maxWidth} × {maxHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageResolutionTooLow'] = 'Resolution is too low';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageResolutionTooHigh'] = 'Resolution is too high';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMinResolution'] = 'Minimum resolution is {minResolution}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMaxResolution'] = 'Maximum resolution is {maxResolution}';
