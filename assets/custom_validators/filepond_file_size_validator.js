import { formatFileSize } from './../utils/filesize.js';

export class FilepondFileSizeValidator {
    static tags = [
        {
            event: 'contao_filepond:process_start',
            priority: 100,
        }
    ];

    async handle(event) {
        const file = event.file;
        const options = event.filepondOptions;

        if (!options.allowFileSizeValidation) {
            return;
        }

        const minFileSize = Number(options.minFileSize) || 0;
        const maxFileSize = Number(options.maxFileSize) || 0;

        const filesize = file.size;
        const filesizeHuman = formatFileSize(filesize);
        const maxFileSizeHuman = formatFileSize(maxFileSize);
        const minFileSizeHuman = formatFileSize(minFileSize);

        // Max file size
        if (maxFileSize && filesize > maxFileSize) {
            const data = {
                filesize,
                filesizeHuman,
                maxFileSize,
                maxFileSizeHuman,
            };

            const message = options.labelMaxFileSizeError.replace(
                /\{(\w+)\}/g,
                (_, key) => data[key] ?? `{${key}}`
            );

            throw new Error(message);
        }

        // Min file size
        if (minFileSize && filesize < minFileSize) {
            const data = {
                filesize,
                filesizeHuman,
                minFileSize,
                minFileSizeHuman,
            };

            const message = options.labelMinFileSizeError.replace(
                /\{(\w+)\}/g,
                (_, key) => data[key] ?? `{${key}}`
            );

            throw new Error(message);
        }
    }
}

