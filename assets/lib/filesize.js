// utils/filesize.js
export function formatFileSize(bytes, decimals = 1) {
    if (!Number.isFinite(bytes) || bytes < 0) {
        return '0 B';
    }

    if (bytes === 0) {
        return '0 B';
    }

    //const k = 1024; // “Binary (IEC units) – formerly used more often”
    const k = 1000;

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    const size = bytes / Math.pow(k, i);
    const fixed = size.toFixed(decimals);

    return `${fixed} ${units[i]}`;
}
