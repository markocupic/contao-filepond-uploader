export class FilepondImageResolutionValidator {
    static tags = [
        {
            event: 'contao_filepond:process_start',
            priority: 90,
        }
    ];

    async handle(event) {
        const file = event.file;
        const options = event.filepondOptions;

        const minImageWidth = Number(event.jsConfig.minImageWidth) || 0;
        const minImageHeight = Number(event.jsConfig.minImageHeight) || 0;
        const maxImageWidth = Number(event.jsConfig.maxImageWidth) || 0;
        const maxImageHeight = Number(event.jsConfig.maxImageHeight) || 0;

        if (!(file instanceof Blob)) {
            throw new Error(options.imageValidateSizeLabelFormatError);
        }

        const {width, height} = await this.loadImage(file);

        if (width < minImageWidth || height < minImageHeight) {
            const data = {width, height, minWidth: minImageWidth, minHeight: minImageHeight};
            const msg = options.validateMinImageResolutionError.replace(/\{(\w+)\}/g, (_, k) => data[k]);
            throw new Error(msg);
        }

        if (width > maxImageWidth || height > maxImageHeight) {
            const data = {width, height, maxWidth: maxImageWidth, maxHeight: maxImageHeight};
            const msg = options.labelMaxImageResolutionValidationError.replace(/\{(\w+)\}/g, (_, k) => data[k]);
            throw new Error(msg);
        }
    }

    loadImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            const img = new Image();

            reader.onload = (e) => {
                img.onload = () => resolve({width: img.width, height: img.height});
                img.onerror = () => reject("Invalid image selected, can't extract resolution data from it.");
                img.src = e.target.result;
            };

            reader.onerror = () => reject("FileReader failed to read the selected image file.");
            reader.readAsDataURL(file);
        });
    }
}
