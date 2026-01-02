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

$GLOBALS['TL_LANG']['ERR']['filepond_file_minheight'] = 'Datei %s benötigt eine Mindesthöhe von %d Pixel!';
$GLOBALS['TL_LANG']['ERR']['filepond_file_minsize'] = 'Datei ist zu klein. Die Mindestgrösse beträgt %s bytes.';
$GLOBALS['TL_LANG']['ERR']['filepond_file_minwidth'] = 'Datei %s benötigt eine Mindestbreite von %d Pixel!';
$GLOBALS['TL_LANG']['ERR']['filepond_general_upload_error'] = 'Beim Versuch die Datei hochzuladen ist es zu einem unbekannten Fehler gekommen.';
$GLOBALS['TL_LANG']['ERR']['filepond_nofileuploaded'] = 'Es wurde keine Datei hochgeladen.';

// Filepond
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelIdle'] = '<span class=\"filepond--label-action\">Hier klicken</span> oder Dateien ablegen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelInvalidField'] = 'Das Uploadfeld enthält ungültige Dateien';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileWaitingForSize'] = 'Warte auf Dateigröße';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileSizeNotAvailable'] = 'Dateigröße nicht verfügbar';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileLoading'] = 'Lade Dateien';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileLoadError'] = 'Datei-Ladefehler';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessing'] = 'Datei wird hochgeladen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingComplete'] = 'Upload abgeschlossen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingAborted'] = 'Upload abgebrochen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingError'] = 'Uploadfehler';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileProcessingRevertError'] = 'Error during revert';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileRemoveError'] = 'Fehler beim Entfernen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelTapToCancel'] = 'Klicken/antippen, um abzubrechen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelTapToRetry'] = 'klicken/antippen, um es erneut zu versuchen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelTapToUndo'] = 'zum Rückgängigmachen antippen/klicken';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonRemoveItem'] = 'Entfernen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonAbortItemLoad'] = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonRetryItemLoad'] = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonAbortItemProcessing'] = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonUndoItemProcessing'] = 'Rückgängig';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonRetryItemProcessing'] = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelButtonProcessItem'] = 'Hochladen';

// Filepond Plugin: File validate type
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelFileTypeNotAllowed'] = 'Datei von ungültigem Typ';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_fileValidateTypeLabelExpectedTypes'] = 'Erwartet {allButLastType} oder {lastType}';

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
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMinImageResolutionValidationError'] = 'Die Bildauflösung {width} x {height} ist zu klein. Minimum sind {minWidth} x {minHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMaxImageResolutionValidationError'] = 'Die Bildauflösung {width} x {height} ist zu gross. Erlaubt sind {maxWidth} x {maxHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_imageValidateSizeLabelFormatError'] = 'Bildtyp nicht unterstützt';

// Custom Filepond Plugin: File validate size
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMaxFileSizeError'] = 'Die maximale Dateigröße wurde überschritten ({filesize} Bytes). Zulässig sind bis zu {maxFileSize} Bytes.';
$GLOBALS['TL_LANG']['MSC']['filepond_trans_labelMinFileSizeError'] = 'Die minimale Dateigröße wurde nicht erreicht ({filesize} Bytes). Erforderlich sind mindestens {minFileSize} Bytes.';
