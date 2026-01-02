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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['filepondUploader'] = '
    {type_legend},type,name,label;
    {fconfig_legend},mandatory,parallelUploads,extensions,multiple,chunkUploads;
    {store_legend:hide},storeFile,addToDbafs;
    {filesize_legend},minlength,maxlength;
    {image_config_legend},minImageWidth,minImageHeight,maxImageWidth,maxImageHeight;
    {image_resize_legend},imgResize;
    {expert_legend:hide},class
';

/**
 * Subpalettes
 */
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['chunkUploads'] = 'chunkSize';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['imgResize'] = 'imgResizeWidth,imgResizeHeight,imgResizeBrowser';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['imgResizeBrowser'] = 'imgResizeModeBrowser,imgResizeUpscaleBrowser';

PaletteManipulator::create()
    ->addField('directUpload', 'storeFile')
    ->applyToSubpalette('storeFile', 'tl_form_field');

/**
 * Selectors
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'chunkUploads';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'imgResize';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'imgResizeBrowser';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['parallelUploads'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'natural', 'maxlength' => 2, 'tl_class' => 'w50'],
    'sql'       => "smallint(2) unsigned NOT NULL default 3",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunkUploads'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr m12', 'submitOnChange' => true],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunkSize'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'natural', 'maxlength' => 10, 'tl_class' => 'w50'],
    'sql'       => "int(10) NOT NULL default 2000000",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['addToDbafs'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['directUpload'] = [
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['minImageWidth'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'rgxp' => 'natural', 'maxlength' => 5, 'tl_class' => 'w50'],
    'sql'       => "smallint(5) unsigned NOT NULL default 0",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['minImageHeight'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'rgxp' => 'natural', 'maxlength' => 5, 'tl_class' => 'w50'],
    'sql'       => "smallint(5) unsigned NOT NULL default 0",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imgResize'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr m12'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imgResizeWidth'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'rgxp' => 'natural', 'maxlength' => 5, 'tl_class' => 'clr w33'],
    'sql'       => "smallint(5) unsigned NOT NULL default 1200",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imgResizeHeight'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'rgxp' => 'natural', 'maxlength' => 5, 'tl_class' => 'w33'],
    'sql'       => "smallint(5) unsigned NOT NULL default 1200",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imgResizeBrowser'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr m12'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imgResizeModeBrowser'] = [
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['force', 'cover', 'contain'],
    'eval'      => ['mandatory' => true, 'rgxp' => 'alpha', 'tl_class' => 'w33'],
    'sql'       => "varchar(255) NOT NULL default 'contain'",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imgResizeUpscaleBrowser'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr m12'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];
