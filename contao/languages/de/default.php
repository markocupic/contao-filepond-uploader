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

$GLOBALS['TL_LANG']['ERR']['fileminwidth'] = 'Datei %s benötigt eine Mindestbreite von %d Pixel!';
$GLOBALS['TL_LANG']['ERR']['fileminheight'] = 'Datei %s benötigt eine Mindesthöhe von %d Pixel!';
$GLOBALS['TL_LANG']['ERR']['filepond.error'] = 'Beim Versuch die Datei hochzuladen ist es zu einem unbekannten Fehler gekommen.';

// Filepond
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelIdle'] = '<span class=\"filepond--label-action\">Hier klicken</span> oder Dateien ablegen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelInvalidField'] = 'Das Uploadfeld enthält ungültige Dateien';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileWaitingForSize'] = 'Warte auf Dateigröße';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileSizeNotAvailable'] = 'Dateigröße nicht verfügbar';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileLoading'] = 'Lade Dateien';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileLoadError'] = 'Datei-Ladefehler';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessing'] = 'Datei wird hochgeladen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingComplete'] = 'Upload abgeschlossen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingAborted'] = 'Upload abgebrochen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingError'] = 'Uploadfehler';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileProcessingRevertError'] = 'Error during revert';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileRemoveError'] = 'Fehler beim Entfernen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelTapToCancel'] = 'Klicken/antippen, um abzubrechen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelTapToRetry'] = 'klicken/antippen, um es erneut zu versuchen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelTapToUndo'] = 'zum Rückgängigmachen antippen/klicken';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonRemoveItem'] = 'Entfernen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonAbortItemLoad'] = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonRetryItemLoad'] = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonAbortItemProcessing'] = 'Abbrechen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonUndoItemProcessing'] = 'Rückgängig';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonRetryItemProcessing'] = 'Wiederholen';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelButtonProcessItem'] = 'Hochladen';

// Filepond Plugin: File validate size
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxFileSizeExceeded'] = 'Datei ist zu groß';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxFileSize'] = 'Maximale Dateigröße ist {filesize}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxTotalFileSizeExceeded'] = 'Maximale Gesamtgröße überschritten';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelMaxTotalFileSize'] = 'Maximale Gesamtdateigröße ist {filesize}';

// Filepond Plugin: File validate type
$GLOBALS['TL_LANG']['MSC']['filepond.trans.labelFileTypeNotAllowed'] = 'Datei von ungültigem Typ';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.fileValidateTypeLabelExpectedTypes'] = 'Erwartet {allButLastType} oder {lastType}';

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
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelFormatError'] = 'Bildtyp nicht unterstützt';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageSizeTooSmall'] = 'Bild ist zu klein';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageSizeTooBig'] = 'Bild ist zu groß';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMinSize'] = 'Mindestgröße ist {minWidth} × {minHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMaxSize'] = 'Die maximale Größe beträgt {maxWidth} × {maxHeight}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageResolutionTooLow'] = 'Auflösung ist zu niedrig';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelImageResolutionTooHigh'] = 'Auflösung ist zu hoch';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMinResolution'] = 'Mindestauflösung ist {minResolution}';
$GLOBALS['TL_LANG']['MSC']['filepond.trans.imageValidateSizeLabelExpectedMaxResolution'] = 'Die maximale Auflösung ist {maxResolution}';
