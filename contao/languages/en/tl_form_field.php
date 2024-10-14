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
    'Drag and drop file uploader based on Filepond by PQINA.',
];

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form_field']['maxConnections'] = [
    'Maximum allowable concurrent requests',
    'Here you can control the maximum allowable concurrent requests per client.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunking'] = [
    'Enable chunking',
    'Enable the file chunking. It is useful to upload big files.',
];
$GLOBALS['TL_LANG']['tl_form_field']['addToDbafs'] = [
    'Add to DBAFS',
    'Add the file to database assisted file system. Note: the widget will return UUID instead of a path.',
];
$GLOBALS['TL_LANG']['tl_form_field']['chunkSize'] = [
    'Chunk size in bytes',
    'Please enter the chunk size in bytes (1MB = 1000000 bytes).',
];
$GLOBALS['TL_LANG']['tl_form_field']['concurrent'] = [
    'Enable concurrent chunking',
    'Activate this checkbox to enable concurrent chunking. Please also note the "Maximum number of connections" setting.',
];
$GLOBALS['TL_LANG']['tl_form_field']['maxWidth'] = [
    'Maximum width (in pixels)',
    'Here you can enter a maximum width of an image in pixels. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['maxHeight'] = [
    'Maximum height (in pixels)',
    'Here you can enter a maximum height of an image in pixels. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['allowImageResize'] = [
    'Enable client side image resizing',
    'Here you can activate the image resizing before the image is uploaded to the server.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeTargetWidth'] = [
    'Image height (in pixels)',
    'Here you can enter an image width. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeTargetHeight'] = [
    'Image height (in pixels)',
    'Here you can enter an image height. Enter 0 to use system defaults.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeMode'] = [
    'Image resize mode',
    'Choose between \'force\', \'cover\', or \'contain\'. Force will ignore the image aspect ratio. Cover will respect the aspect ratio and will scale to fill the target dimensions. Contain also respects the aspect ratio and will fit the image inside the set dimensions. All three settings will upscale images when there are smaller then the given target dimensions.',
];
$GLOBALS['TL_LANG']['tl_form_field']['imageResizeUpscale'] = [
    'Upscale image width and height',
    'Deactivate the checkbox to prevent the upscaling of images that are smaller than the target size.',
];
