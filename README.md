<img src="docs/logo.png?raw=true" alt="marko cupic" width="200">

# [Filepond file uploader](https://pqina.nl/filepond) for Contao CMS

|                                 |                                 |
|---------------------------------|---------------------------------|
| ![Filepond](docs/frontend2.png) | ![Filepond](docs/frontend1.png) |

This extension provides a file uploader widget for the Contao form generator.
You can use **client side image resizing** and **chunkUploads** for uploading large files.

## Create the form widget from DCA

For custom use cases you can embed the widget within a Codefog Haste Form (see below).

### Example frontend module controller with a minimum configuration

```
<?php

declare(strict_types=1);

namespace App\Controller\FrontendModule;

use Codefog\HasteBundle\Form\Form;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'miscellaneous')]
class ExampleController extends AbstractFrontendModuleController
{
    public function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $form = $this->getForm();

        if ($form->isSubmitted() && $form->validate() && !$request->isXmlHttpRequest()) {
            throw new RedirectResponseException($request->getUri());
        }

        $template->set('form', $form->generate());

        return $template->getResponse();
    }

    private function getForm(): Form
    {
        $form = new Form('MyFileUploadForm', 'POST');

        $form->addFormField('filepond', [
            'inputType' => FilepondFrontendWidget::TYPE,
            'eval' => [
                'extensions' => 'jpg,jpeg,png',
                'mandatory' => true,
                'uploadFolder' => 'files/filepond_test', // If no upload folder ist set, the file will be stored in the Contao upload folder -> /system/tmp
                'storeFile' => true,
                'addToDbafs' => true,
                'multiple' => true,
            ],
        ]);

        // Add a "submit" button
        $form->addSubmitFormField('Absenden');

        return $form;
    }
}

```

### Full configuration

```
$form = new \Codefog\HasteBundle\Form\Form('MyFileUploadForm', 'POST');

// Add a sample text form field:
$form->addFormField('filepond', [
    'inputType' => filepondUploader,
    'eval'      => [
        'label' => 'My Filepond Uploader',
        'mandatory' => true,
        'uploadFolder' => 'files/gallery', // Relative path to the target folder
        'multiple' => true, // Do allow multiple files beeing selected & uploaded.
        'mSize' => 10, // Allowed upload number (multiple must be set to "true")
        'storeFile' => true, // Save file to the filesystem
        'doNotOverwrite' => true, // Do not overwrite files with equal filenames
        'addToDbafs' => true, // Add uploaded file to the database assisted filesystem (DBAFS)
        'minlength' => 1000000, // Minimum file size (bytes)
        'maxlength' => 10000000, // Maximum file size (bytes)
        'chunkUploads' => true, // Enable chunkUploads (large files)
        'chunkSize' => 2000000, // Chunk size (bytes)
        'extensions' => 'jpg,jpeg,png', // Accepted extensions
        'parallelUploads' => 3, // Maximum number of simultaneous uploads
         // Validate image resolution upon upload
        'minImageWidth' => 1000, // Minimum width for images (pixels)
        'minImageHeight' => 1000, // Minimum height for images (pixels)
        'maxImageWidth' => 2000, // Maximum width for images (pixels)
        'maxImageHeight' => 2000, // Maximum width for images (pixels)
        // Server side image resizing
        'imgResize' => true, // Allow image resizing (server side)
        'imgResizeWidth' => 1000 // Image will be resized server side to this width (pixels)
        'imgResizeHeight' => 1000 // Image will be resized server side to this height (pixels)
        // Client side image resizing
        'imgResizeBrowser' => true, // Allow client side image resizing! 'imgResize' must be set to "true" as well!
        'imgResizeModeBrowser' => 'contain', // Use "contain", "force", "contain" -> https://pqina.nl/filepond/docs/api/plugins/image-resize/#properties
        'imgResizeUpscaleBrowser' => false, // Set to false to prevent upscaling of images smaller than the target size
    ],
]);
```
