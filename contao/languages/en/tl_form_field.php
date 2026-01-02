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

$GLOBALS['TL_LANG']['FFL']['filepondUploader'] = [
    'Filepond uploader',
    'Drag and drop file uploader based on Filepond by PQINA.',
];

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_form_field']['image_config_legend'] = 'Settings for image width and height';
$GLOBALS['TL_LANG']['tl_form_field']['filesize_legend'] = 'Settings for allowed file size';
$GLOBALS['TL_LANG']['tl_form_field']['image_resize_legend'] = 'Settings for reducing image resolution during upload';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['parallelUploads'] = [
    'Maximum allowable concurrent requests',
    'Here you can control the maximum allowable concurrent requests per client.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunkUploads'] = [
    'Enable chunkUploads',
    'Enable the file chunkUploads. It is useful to upload big files.',
];
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs'] = [
    'Add to DBAFS',
    'Add the file to database assisted file system. Note: the widget will return UUID instead of a path.',
];
$GLOBALS['TL_LANG']['tl_form_field']['directUpload'] = [
    'Upload files directly',
    'Upload files directly to the target directory without submitting the form.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = [
    'Chunk size in bytes',
    'Please enter the chunk size in bytes (1MB = 1000000 bytes).',
];
$GLOBALS['TL_LANG']['tl_form_field']['minImageWidth'] = [
    'Minimum image width',
    'Here you can define the minimum width for image uploads in pixels.',
];
$GLOBALS['TL_LANG']['tl_form_field']['minImageHeight'] = [
    'Minimum image height',
    'Here you can define the minimum height for image uploads in pixels.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imgResize'] = [
    'Enable image resizing',
    'Here you can enable image resizing.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imgResizeWidth'] = [
    'Image width (in pixels)',
    'Here you can enter an image width. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imgResizeHeight'] = [
    'Image height (in pixels)',
    'Here you can enter an image height. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imgResizeBrowser'] = [
    'Enable browser-side image resizing',
    'Here you can enable resizing of the image before it is uploaded to the server.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imgResizeModeBrowser'] = [
    'Image resize mode',
    'Choose between \'force\', \'cover\', or \'contain\'. Force will ignore the image aspect ratio. Cover will respect the aspect ratio and will scale to fill the target dimensions. Contain also respects the aspect ratio and will fit the image inside the set dimensions. All three settings will upscale images when they are smaller then the given target dimensions.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imgResizeUpscaleBrowser'] = [
    'Upscale image width and height',
    'Deactivate the checkbox to prevent the upscaling of images that are smaller than the target size.',
];
