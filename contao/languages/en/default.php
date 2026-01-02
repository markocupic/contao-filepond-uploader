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

$GLOBALS['TL_LANG']['ERR']['filepond_file_minheight'] = 'File %s must have a minimum height of %d pixels!';
$GLOBALS['TL_LANG']['ERR']['filepond_file_minsize'] = 'The file is too small. The minimum size is %s bytes.';
$GLOBALS['TL_LANG']['ERR']['filepond_file_minwidth'] = 'File %s must have a minimum width of %d pixels!';
$GLOBALS['TL_LANG']['ERR']['filepond_general_upload_error'] = 'An unknown error occurred while trying to upload the file.';
$GLOBALS['TL_LANG']['ERR']['filepond_nofileuploaded'] = 'No file was uploaded.';

// Filepond
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelIdle'] = 'Drag & Drop your files or <span class=\"filepond--label-action\">Browse</span>';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelInvalidField'] = 'Field contains invalid files';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileWaitingForSize'] = 'Waiting for size';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileSizeNotAvailable'] = 'Size not available';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileLoading'] = 'Loading';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileLoadError'] = 'Error during load';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessing'] = 'Uploading';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingComplete'] = 'Upload complete';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingAborted'] = 'Upload cancelled';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingError'] = 'Error during upload';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingRevertError'] = 'Error during revert';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileRemoveError'] = 'Error during remove';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelTapToCancel'] = 'tap to cancel';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelTapToRetry'] = 'tap to retry';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelTapToUndo'] = 'tap to undo';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonRemoveItem'] = 'Remove';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonAbortItemLoad'] = 'Abort';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonRetryItemLoad'] = 'Retry';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonAbortItemProcessing'] = 'Cancel';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonUndoItemProcessing'] = 'Undo';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonRetryItemProcessing'] = 'Retry';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonProcessItem'] = 'Upload';

// Filepond Plugin: File validate type
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileTypeNotAllowed'] = 'File of invalid type';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_fileValidateTypeLabelExpectedTypes'] = 'Expects {allButLastType} or {lastType}';

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

// Custom Filepond Plugin: Image validate resolution
$GLOBALS['TL_LANG']['MSC']['filepond_trans_imageValidateSizeLabelFormatError'] = 'Image type not supported';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMinImageResolutionValidationError'] = 'The image resolution {width} x {height} is too small. The minimum is {minWidth} x {minHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMaxImageResolutionValidationError'] = 'The image resolution {width} x {height} is too large. The max. allowed resolution is {maxWidth} x {maxHeight}.';

// Custom Filepond Plugin: File validate size
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMaxFileSizeError'] = 'The maximum file size has been exceeded ({filesize} bytes). Up to {maxFileSize} bytes are allowed.';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMinFileSizeError'] = 'The minimum file size has not been reached ({filesize} bytes). At least {minFileSize} bytes are required.';
