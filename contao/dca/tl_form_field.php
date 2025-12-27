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
    {fconfig_legend},mandatory,maxConnections,extensions,minlength,maxlength,multiple,chunking;
    {image_config_legend},maxImageWidth,maxImageHeight,allowImageResize;
    {store_legend:hide},storeFile,addToDbafs;
    {expert_legend:hide},class,fSize
';

/**
 * Subpalettes
 */
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['chunking'] = 'chunkSize';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['allowImageResize'] = 'imageResizeTargetWidth,imageResizeTargetHeight,imageResizeMode,imageResizeUpscale';

PaletteManipulator::create()
    ->addField('directUpload', 'storeFile')
    ->applyToSubpalette('storeFile', 'tl_form_field');

/**
 * Selectors
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'chunking';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'allowImageResize';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['maxConnections'] = [
    'default'   => 3,
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'natural', 'tl_class' => 'w50'],
    'sql'       => "int(10) NOT NULL default '3'",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunking'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr m12', 'submitOnChange' => true],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['chunkSize'] = [
    'default'   => 2000000,
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql'       => "varchar(16) NOT NULL default ''",
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

$GLOBALS['TL_DCA']['tl_form_field']['fields']['allowImageResize'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr m12'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imageResizeTargetWidth'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'clr w33'],
    'sql'       => "smallint(5) unsigned NOT NULL default 1500",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imageResizeTargetHeight'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'w33'],
    'sql'       => "smallint(5) unsigned NOT NULL default 1500",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imageResizeMode'] = [
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['force', 'cover', 'contain'],
    'eval'      => ['mandatory' => true, 'rgxp' => 'alpha', 'tl_class' => 'w33'],
    'sql'       => "varchar(255) NOT NULL default 'contain'",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['imageResizeUpscale'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr m12'],
    'sql'       => ['type' => 'boolean', 'default' => false],
];
