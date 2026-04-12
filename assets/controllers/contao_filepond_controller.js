import {Controller} from '@hotwired/stimulus';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import * as FilePond from 'filepond/dist/filepond.esm.js';
import {EventDispatcher} from './../lib/event_dispatcher.js';
import {ListenerProvider} from './../lib/listener_provider.js';

// Import the filepond core styles
import 'filepond/dist/filepond.min.css';

// Import the plugin styles
import '../css/contao_filepond_plugin.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import 'filepond-plugin-image-edit/dist/filepond-plugin-image-edit.css';

// --- Module-level singletons – initialized once per JS bundle load -------

const listenerProvider = new ListenerProvider();
const eventDispatcher = new EventDispatcher(listenerProvider);

const modules = import.meta.webpackContext('./../custom_validators', {
    recursive: false,
    regExp: /\.js$/,
});

modules.keys().forEach((key) => {
    const mod = modules(key);
    for (const exported of Object.values(mod)) {
        if (typeof exported === 'function' && Array.isArray(exported.tags)) {
            const instance = new exported();
            for (const tag of exported.tags) {
                listenerProvider.register(tag.event, instance, tag.priority ?? 0);
            }
        }
    }
});

const PLUGINS = [
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginImagePreview,
    FilePondPluginImageExifOrientation,
    FilePondPluginImageEdit,
    FilePondPluginFileValidateType,
];

// Guard so registerPlugin() is only called once (avoids console warnings on
// repeated Turbo navigations where the module stays alive).
let pluginsRegistered = false;

// --- Stimulus Controller --------------------------------------------
//
// Expected HTML:
//
//   <div class="filepond-wrapper"
//        data-controller="contao-filepond"
//        data-contao-filepond-name-value="upload"
//        data-contao-filepond-config-value='{"extensions":"image/*", ...}'>
//     <input type="file" data-contao-filepond-target="input" />
//   </div>

export default class extends Controller {
    static targets = ['input'];

    static values = {
        name: String,
        config: Object,
    };

    #pondInstance = null;
    #allowMultiple = false;
    #options = {};
    #busyInterval = null;

    // Arrow function so `this` is correct when used as an event listener.
    #handleBeforeCache = () => {
        this.#destroyPond();
    };

    // --- Lifecycle --------------------------------------------

    connect() {
        if (!pluginsRegistered) {
            FilePond.registerPlugin(...PLUGINS);
            pluginsRegistered = true;
        }

        // Destroy FilePond before Turbo snapshots the page so the cache
        // contains the plain <input> – not the FilePond UI.  Without this,
        // navigating back would restore a broken FilePond shell and connect()
        // would try to initialize on a node that is no longer an <input>.
        document.addEventListener('turbo:before-cache', this.#handleBeforeCache);

        this.#prepareInputElement();
        this.#buildOptions();
        this.#pondInstance = FilePond.create(this.inputTarget, this.#options);
    }

    disconnect() {
        document.removeEventListener('turbo:before-cache', this.#handleBeforeCache);
        this.#destroyPond();
    }

    // --- Private helpers --------------------------------------------

    #destroyPond() {
        if (this.#busyInterval !== null) {
            clearInterval(this.#busyInterval);
            this.#busyInterval = null;
        }
        if (this.#pondInstance) {
            this.#pondInstance.destroy();
            this.#pondInstance = null;
        }
    }

    get #jsConfig() {
        return this.configValue;
    }

    get #name() {
        return this.nameValue;
    }

    #prepareInputElement() {
        const input = this.inputTarget;
        const cfg = this.#jsConfig;

        input.setAttribute('accept', cfg.extensions);

        if (cfg.multiple) {
            if (cfg.limit === 1) {
                input.setAttribute('data-max-files', 1);
                input.setAttribute('name', this.#name);
                this.#allowMultiple = false;
            } else if (cfg.limit > 1) {
                input.setAttribute('multiple', '');
                input.setAttribute('data-max-files', cfg.limit);
                input.setAttribute('name', this.#name + '[]');
                this.#allowMultiple = true;
            } else {
                // Infinite uploads allowed
                input.setAttribute('multiple', '');
                input.setAttribute('name', this.#name + '[]');
                this.#allowMultiple = true;
            }
        } else {
            input.setAttribute('data-max-files', 1);
            input.setAttribute('name', this.#name);
            this.#allowMultiple = false;
        }

        if (cfg.minFileSizeLimit) {
            input.setAttribute('data-min-file-size', cfg.minFileSizeLimit);
        }

        if (cfg.maxFileSizeLimit) {
            input.setAttribute('data-max-file-size', cfg.maxFileSizeLimit);
        }
    }

    #buildOptions() {
        const cfg = this.#jsConfig;

        this.#options = {
            ...cfg.translations,
            maxParallelUploads: cfg.parallelUploads < 1 ? 1 : cfg.parallelUploads,
            instantUpload: true,
            allowMultiple: this.#allowMultiple,
            allowFileTypeValidation: true,
            allowRevert: !cfg.directUpload,
            allowRemove: !cfg.directUpload,
            oninit: () => this.#oninit(),
            onaddfile: (err, item) => this.#onaddfile(err, item),
            onaddfilestart: (file) => this.#onaddfilestart(file),
            onprocessfile: (err, file) => this.#onprocessfile(err, file),
            server: {
                process: async (fieldName, file, metadata, load, error, progress, abort) => {
                    const itemId = metadata.itemId;
                    const event = {
                        itemId,
                        fieldName,
                        file,
                        metadata,
                        filepondOptions: this.#options,
                        jsConfig: cfg,
                        resolve() {
                        },
                        reject(_err) {
                        },
                    };

                    try {
                        await eventDispatcher.dispatch('contao_filepond:process_start', event);
                        await this.#contaoUpload(fieldName, file, metadata, load, error, progress, abort);
                    } catch (err) {
                        this.#displayError(itemId, err.message, error);
                    }
                },
                revert: (transferKey, load, error) => {
                    this.#revertUpload(transferKey, load, error);
                },
                fetch: null,
            },
            fileValidateTypeDetectType: (source, _type) => {
                return new Promise((resolve) => {
                    const extension = `.${source.name.split('.').pop().toLowerCase()}`;
                    resolve(extension);
                });
            },
        };

        this.#options.allowFileSizeValidation = true;
        this.#options.minFileSize = cfg.minFileSizeLimit;
        this.#options.maxFileSize = cfg.maxFileSizeLimit;

        if (cfg.imgResize && cfg.imgResizeBrowser && cfg.imgResizeWidth > 0 && cfg.imgResizeHeight > 0) {
            this.#options.allowImageResize = true;
            this.#options.imageResizeTargetWidth = cfg.imgResizeWidth;
            this.#options.imageResizeTargetHeight = cfg.imgResizeHeight;
            this.#options.imageResizeMode = cfg.imgResizeModeBrowser;
            this.#options.imageResizeUpscale = cfg.imgResizeUpscaleBrowser;
        }
    }

    #oninit() {
        this.element.classList.add('filepond--is-ready');
        // Poll for upload completion and remove the busy marker class.
        this.#busyInterval = setInterval(() => {
            if (!this.element.querySelector('.filepond--item[data-filepond-item-state="busy processing"]')) {
                this.element.classList.remove('filepond--is-busy');
            }
        }, 1000);
    }

    #onaddfile(_err, item) {
        item.setMetadata('itemId', item.id);
        item.setMetadata('options', this.#options);
    }

    #onaddfilestart(_file) {
        this.element.classList.add('filepond--is-busy');
    }

    // eslint-disable-next-line no-unused-vars
    #onprocessfile(_err, _file) {
    }

    #displayError(itemId, message, error) {
        error(message);

        const fileStatusBox = document.querySelector(
            `#filepond--item-${itemId} .filepond--file-status`
        );
        const fileStatusMainBox = fileStatusBox?.querySelector('.filepond--file-status-main');

        fileStatusBox?.querySelector('.filepond--contao-error')?.remove();

        if (fileStatusMainBox) {
            const errorBox = document.createElement('span');
            errorBox.className = 'filepond--contao-error';
            errorBox.style.fontSize = '0.75rem';
            errorBox.textContent = message;
            fileStatusMainBox.after(errorBox);
        }
    }

    async #contaoUpload(fieldName, file, metadata, load, error, progress, abort) {
        const itemId = metadata.itemId;
        const cfg = this.#jsConfig;

        // Remove any stale error boxes from a previous attempt
        document.querySelectorAll(`#filepond--item-${itemId} .filepond--contao-error`)
            .forEach((el) => el.remove());

        const doChunkedUpload =
            cfg.chunkUploads === true &&
            cfg.chunkSize > 0 &&
            file.size > cfg.chunkSize;

        // --- Normal upload --------------------------------------------
        if (!doChunkedUpload) {
            const buffer = await file.arrayBuffer();
            const fileChecksum = await this.#sha256(buffer);

            const formData = new FormData();
            formData.append(fieldName, file, file.name);
            formData.append('REQUEST_TOKEN', cfg.csrfToken);
            formData.append('filePondItemId', itemId);
            formData.append('fileChecksum', fileChecksum);

            const request = new XMLHttpRequest();
            request.open('POST', window.location.href);
            request.setRequestHeader('Accept', 'application/json');
            request.setRequestHeader('name', this.#name);
            request.setRequestHeader('action', 'filepond_upload');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.setRequestHeader('filePondItemId', itemId);

            request.upload.onprogress = (e) => {
                progress(e.lengthComputable, e.loaded, e.total);
            };

            request.onload = () => {
                if (request.status >= 200 && request.status < 300) {
                    const json = JSON.parse(request.response);
                    if (json.success === false) {
                        this.#displayError(itemId, json.error ?? 'Upload failed with error code 1.', error);
                    } else if (json.success === true) {
                        load(json.transferKey);
                    } else {
                        this.#displayError(itemId, 'Upload failed with error code 2.', error);
                    }
                } else {
                    this.#displayError(itemId, 'Upload failed with error code 3.', error);
                }
            };

            request.send(formData);

            return {
                abort: () => {
                    request.abort();
                    abort();
                },
            };
        }

        // --- Chunked upload --------------------------------------------
        let offset = 0;
        let aborted = false;
        let activeAbortController = null;
        const {chunkSize} = cfg;

        const buffer = await file.arrayBuffer();
        const fileChecksum = await this.#sha256(buffer);

        const uploadFileInChunks = async () => {
            if (aborted) return;

            const chunk = file.slice(offset, offset + chunkSize);
            const formData = new FormData();
            formData.append('REQUEST_TOKEN', cfg.csrfToken);
            formData.append(fieldName.replace(/\[\]$/, '') + '_chunk', chunk);
            formData.append('fileChecksum', fileChecksum);
            formData.append('fileName', file.name);
            formData.append('offset', offset);
            formData.append('totalSize', file.size);

            const controller = new AbortController();
            activeAbortController = controller;

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        name: this.#name,
                        action: 'filepond_upload_chunk',
                        'X-Requested-With': 'XMLHttpRequest',
                        filePondItemId: itemId,
                    },
                    body: formData,
                    signal: controller.signal,
                });

                if (!response.ok) {
                    error('Upload error');
                    return;
                }

                const json = await response.json();

                if (json.success === false) {
                    this.#displayError(itemId, json.error ?? 'Chunk upload failed with error code 1.', error);
                    return;
                }

                if (json.success !== true) {
                    this.#displayError(itemId, 'Chunk upload failed with error code 2.', error);
                    return;
                }

                offset += chunk.size;
                progress(true, offset, file.size);

                if (offset < file.size) {
                    await uploadFileInChunks();
                } else {
                    load(json.directUpload === true ? '' : json.transferKey);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    error('Network error');
                }
            }
        };

        uploadFileInChunks();

        return {
            abort: () => {
                aborted = true;
                activeAbortController?.abort();
                abort();
            },
        };
    }

    async #revertUpload(transferKey, load, error) {
        try {
            const response = await fetch(window.location.href, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'text/plain',
                    name: this.#name,
                    action: 'filepond_upload_revert',
                    accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: transferKey,
            });

            if (!response.ok) {
                throw new Error('Revert failed');
            }

            load();
        } catch (_err) {
            error('Could not revert file.');
        }
    }

    async #sha256(buffer) {
        const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
        return [...new Uint8Array(hashBuffer)]
            .map((b) => b.toString(16).padStart(2, '0'))
            .join('');
    }
}
