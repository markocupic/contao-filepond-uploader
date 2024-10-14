<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\Controller\FrontendModule;

use Codefog\HasteBundle\Form\Form;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Markocupic\ContaoFilepondUploader\Widget\FrontendWidget;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'miscellaneous')]
class FilepondController extends AbstractFrontendModuleController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $form = $this->getForm();

        if (!empty($_POST)) {
            $form->validate();
        }

        $template->set('form', $form->generate());

        return $template->getResponse();
    }

    private function getForm(): Form
    {
        $form = new Form('MyFileUploadForm', 'POST');

        // Add a sample text form field:
        $form->addFormField('filepond', [
            'inputType' => FrontendWidget::TYPE,
            'eval' => [
                'mandatory' => true,
                'uploadFolder' => 'files/_filepond_test_2',
                'extensions' => 'jpg,jpeg,JPEG,JPG,png,PNG',
                'storeFile' => true,
                /*
                 * 'doNotOverwrite' => true,
                 * 'addToDbafs' => true,
                 * 'maxlength' => 100000000000,
                 * 'minlength' => 100,
                 * 'allowImageResize' => true,
                 * 'imageResizeTargetWidth' => 2000,
                 * 'imageResizeTargetHeight' => 2000,
                 * 'imageResizeMode' => 'contain',
                 * 'imageResizeUpscale' => false,
                 * 'minWidth' => 100,
                 * 'maxWidth' => 150000,
                 * 'multiple' => false,
                 * 'mSize' => 10,
                 */
            ],
        ]);

        // Add a submit field
        $form->addSubmitFormField('Absenden');

        return $form;
    }
}
