![Alt text](docs/logo.png?raw=true "logo")

# Welcome to Contao Filepond Uploader



## Create the form widget from DCA

### Minimum configuration
```
$form = new \Codefog\HasteBundle\Form\Form('MyFileUploadForm', 'POST');

$form->addFormField('filepond', [
    'inputType' => filepondUploader,
    'eval'      => [
        'uploadFolder' => 'files/gallery', // Relative path to the target folder
        'storeFile' => true,
    ],
]);
```

### Full configuration
```
$form = new \Codefog\HasteBundle\Form\Form('MyFileUploadForm', 'POST');

// Add a sample text form field:
$form->addFormField('filepond', [
    'inputType' => filepondUploader,
    'eval'      => [
        'mandatory' => true,
        'uploadFolder' => 'files/gallery', // Relative path to the target folder
        'multiple' => false, // Do not allow multiple files beeing uploaded.
        'mSize' => 10, // Allowed upload number (multiple must be set to "true")
        'storeFile' => true, // Save file to the filesystem
        'doNotOverwrite' => true, // Do not overwrite files with equal filenames
        'addToDbafs' => true, // Add uploaded file to the database assisted filesystem (DBAFS)
        'minlength' => 1000000, // Minimum file size (bytes)
        'maxlength' => 10000000, // Maximum file size (bytes)
        'extensions' => 'jpg,jpeg,JPEG,JPG,png,PNG', // Accepted extensions
         // Images
        'minWidth' => 1000, // Minimum width for images (pixels)
        'maxWidth' => 5000, // Maximum width for images (pixels)
        'allowImageResize' => true, // Allow client side image resizing
        'imageResizeTargetWidth' => 2000, // Resized image width
        'imageResizeTargetHeight' => 2000, // Resized image height
        'imageResizeMode' => 'contain', // Use "contain", "force", "contain" -> https://pqina.nl/filepond/docs/api/plugins/image-resize/#properties
        'imageResizeUpscale' => false, // Set to false to prevent upscaling of images smaller than the target size
    ],
]);
```
