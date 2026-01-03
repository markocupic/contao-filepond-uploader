export class FilepondImageResolutionValidator {
    static tags = [
        {
            event: 'contao_filepond:process_start',
            priority: 90,
        }
    ];

    async handle(event) {
        const file = event.file;

        // Skip non-images early
        if (!this.isImage(file)) {
            return;
        }

        const options = event.filepondOptions;

        const minImageWidth = Number(event.jsConfig.minImageWidth) || 0;
        const minImageHeight = Number(event.jsConfig.minImageHeight) || 0;
        const maxImageWidth = Number(event.jsConfig.maxImageWidth) || 0;
        const maxImageHeight = Number(event.jsConfig.maxImageHeight) || 0;

        if (!(file instanceof Blob)) {
            throw new Error(options.labelImageValidateSizeLabelFormatError);
        }

        // Load the image
        let width, height;

        try {
            ({width, height} = await this.loadImage(file));
        } catch (e) {
            console.log(`Skip resolution validator: Could not load image from ${file.name}.`);
            return;
        }

        // Skip if width or height is not a number
        if (!Number.isFinite(width) || !Number.isFinite(height)) {
            console.log(`Skip resolution validator: Invalid resolution for ${file.name}.`);
            return;
        }

        // Min resolution
        if (width < minImageWidth || height < minImageHeight) {
            const data = {'width': width, 'height': height, 'minWidth': minImageWidth, 'minHeight': minImageHeight};
            const msg = options.labelMinImageResolutionValidationError.replace(/\{(\w+)\}/g, (_, k) => data[k]);
            throw new Error(msg);
        }

        // Max resolution
        if (maxImageWidth && (width > maxImageWidth || height > maxImageHeight)) {
            const data = {'width': width, 'height': height, 'maxWidth': maxImageWidth, 'maxHeight': maxImageHeight};
            const msg = options.labelMaxImageResolutionValidationError.replace(/\{(\w+)\}/g, (_, k) => data[k]);
            throw new Error(msg);
        }
    }

    loadImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(url);
                resolve({width: img.naturalWidth, height: img.naturalHeight});
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error("Invalid image, cannot extract resolution."));
            };

            img.src = url;
        });
    }

    isImage(file) {
        // MIME type OR extension fallback
        return (
            /^image\//.test(file.type) ||
            /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(file.name)
        );
    }
}
