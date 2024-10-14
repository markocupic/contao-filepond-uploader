<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

$GLOBALS['TL_LANG']['FFL']['filepondUploader'] = [
    'Filepond uploader',
    'Drag and drop Dateiuploader basierend auf dem Filepond Uploader von PQINA.',
];

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['maxConnections'] = [
    'Maximale Anzahl an Verbindungen',
    'Geben Sie hier ein wie viele Verbindungen der gleiche Client maximal haben darf.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunking'] = [
    'Chunking aktivieren',
    'Aktivieren Sie Chunking, wenn Sie grosse Dateien hochladen möchten.',
];
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs'] = [
    'Zum DBAFS hinzufügen',
    'Die Datei zum datenbankunterstützten Dateisystem hinzufügen. Bitte beachten: In diesem Fall gibt das Formularfeld eine UUID statt dem Pfad zurück.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = [
    'Chunk-Grösse in Bytes',
    'Bitte geben Sie die Chunk-Grösse in Bytes ein  (1MB = 1000000 Bytes).',
];
$GLOBALS['TL_LANG']['tl_form_field']['concurrent'] = [
    'Simultanes Hochladen aktivieren',
    'Aktivieren Sie hier den simultanen Dateiupload. Beachten Sie auch die "Maximale Anzahl an Verbindungen" Einstellungsmöglichkeit.',
];
$GLOBALS['TL_LANG']['tl_form_field']['allowImageResize'] = [
    'Client-seitige Bildgrößenänderung aktivieren',
    'Hier können Sie die Größenänderung des Bildes aktivieren, bevor das Bild auf den Server hochgeladen wird.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeTargetWidth'] = [
    'Bildhöhe (in Pixel)',
    'Hier können Sie eine Bildbreite eingeben. Geben Sie 0 ein, um die Systemvorgaben zu verwenden.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeTargetHeight'] = [
    'Bildhöhe (in Pixel)',
    'Hier können Sie eine Bildhöhe eingeben. Geben Sie 0 ein, um die Systemvorgaben zu verwenden.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeMode'] = [
    'Bildgrößenänderungsmodus',
    'Wählen Sie zwischen \'force\', \'cover\', oder \'contain\'. Bei Force wird das Seitenverhältnis des Bildes ignoriert. Cover berücksichtigt das Seitenverhältnis und skaliert so, dass es die Zieldimensionen ausfüllt. Contain beachtet ebenfalls das Seitenverhältnis und passt das Bild in die festgelegten Abmessungen ein. Bei allen drei Einstellungen werden Bilder hochskaliert, wenn sie kleiner sind als die angegebenen Zielmaße.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeUpscale'] = [
    'Breite und Höhe des Bildes hochskalieren',
    'Deaktivieren Sie die Checkbox, um das Hochskalieren von Bildern zu verhindern, die kleiner als die Zielgröße sind.',
];
