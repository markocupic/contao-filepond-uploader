// ResizeImageListener.js
export class FilepondFileSizeValidator {
    static tags = [
        {
            event: 'contao_filepond:process_start',
            priority: 100,
        }
    ];

    handle(event) {
        const file = event.file;
        const options = event.filepondOptions;

        if (true !== options.allowFileSizeValidation ?? false) {
            return;
        }

        const minFileSize = Number(options.minFileSize) || 0;
        const maxFileSize = Number(options.maxFileSize) || 0;

        if (maxFileSize && file.size > maxFileSize) {
            const data = {'filesize': file.size, 'maxFileSize': maxFileSize};
            const errMessage = options.labelMaxFileSizeError.replace(/\{(\w+)\}/g, (_, key) => data[key]);

            throw new Error(errMessage);
        }

        if (minFileSize && file.size < minFileSize) {
            const data = {'filesize': file.size, 'minFileSize': minFileSize};
            const errMessage = options.labelMinFileSizeError.replace(/\{(\w+)\}/g, (_, key) => data[key]);

            throw new Error(errMessage);
        }
    }
}
