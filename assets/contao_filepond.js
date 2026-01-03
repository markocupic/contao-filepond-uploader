// Import all required original FilePond plugins
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';

// Load the FilePond core library
import * as FilePond from 'filepond/dist/filepond.esm.js';

// Import the event dispatcher and listener provider classes.
// We need these to dynamically discover and register our custom validators.
import {EventDispatcher} from './event_dispatcher.js';
import {ListenerProvider} from './listener_provider.js';

// Create the central listener provider.
// This object stores all registered listeners grouped by event name.
const listenerProvider = new ListenerProvider();

// Create the event dispatcher.
// It executes listeners SEQUENTIALLY based on their priority.
const eventDispatcher = new EventDispatcher(listenerProvider);

// Dynamically import all custom FilePond plugin modules.
// Each module in ./custom_filepond_plugins/*.js may export one or more listener classes.
//
// This mechanism allows you to simply drop a new validator file into the folder
// (e.g. image resolution validator, max file size validator, MIME type validator)
// and it will automatically be discovered and registered.
const modules = import.meta.webpackContext('./custom_validators', {
    recursive: false,
    regExp: /\.js$/,
});

// Iterate over all discovered modules
modules.keys().forEach((key) => {
    const mod = modules(key);

    // Each module may export multiple classes.
    // We check each export to see if it is a listener class.
    for (const exported of Object.values(mod)) {

        // A listener class is identified by:
        // - being a function (constructor)
        // - having a static "tags" array that defines which events it listens to
        if (typeof exported === 'function' && Array.isArray(exported.tags)) {

            // Instantiate the listener class
            const instance = new exported();

            // Register the listener for each declared tag.
            // A tag defines:
            // - event: the event name (e.g. 'contao_filepond:process_start')
            // - priority: execution order (higher runs earlier)
            //
            // This is where validators such as:
            // - image resolution validation
            // - maximum file size validation
            // - MIME type validation
            // are added to the pipeline.
            for (const tag of exported.tags) {
                listenerProvider.register(tag.event, instance, tag.priority ?? 0);
            }
        }
    }
});

export default class ContaoFilepond {
    wrapper = null;
    widget = null;
    name = null;
    jsConfig = null;
    #allowMultiple = false;
    #pond = null;
    #options = {
        imageResizeTargetWidth: 1200,
        imageResizeTargetHeight: 1200,
    };
    #plugins = [
        FilePondPluginImageResize,
        FilePondPluginImageTransform,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginImageEdit,
        FilePondPluginFileValidateType,

        // We do not want to use the built-in plugins from FilePond,
        // as these are executed before the images are resized.
        // However, the file size and resolution of the images
        // should be checked after the image has been resized.
        //
        //FilePondPluginImageValidateSize,
        //FilePondPluginValidateImageResolution,
    ];

    /**
     *
     * @param widget file input element
     * @param name
     * @param jsConfig
     */
    constructor(widget, name, jsConfig) {
        this.wrapper = widget.closest('.filepond-wrapper');
        this.widget = widget;
        this.name = name;
        this.jsConfig = jsConfig;

        this.#pond = FilePond;

        this.#init();
    }

    #init() {

        // Set the accept attribute on the filepond input field.
        this.setAllowedExtensions(this.jsConfig.extensions);

        // Add the name, the multiple and the data-max-files attribute on the filepond input field.
        if (this.jsConfig.multiple) {
            if (this.jsConfig.limit === 1) {
                this.setAttribute('data-max-files', 1);
                this.setAttribute('name', this.name);
                this.#allowMultiple = false;
            } else if (this.jsConfig.limit > 1) {
                this.setAttribute('multiple', '');
                this.setAttribute('data-max-files', this.jsConfig.limit);
                this.setAttribute('name', this.name + '[]');
                this.#allowMultiple = true;
            } else {
                // Infinite file uploads allowed
                this.setAttribute('multiple', '');
                this.setAttribute('name', this.name + '[]');
                this.#allowMultiple = true;
            }
        } else {
            this.setAttribute('data-max-files', 1);
            this.setAttribute('name', this.name);
            this.#allowMultiple = false;
        }

        // Set the data-min-file-size attribute on the filepond input field.
        if (this.jsConfig.minFileSizeLimit) {
            this.setAttribute('data-min-file-size', this.jsConfig.minFileSizeLimit);
        }

        // Set the data-max-file-size attribute on the filepond input field.
        if (this.jsConfig.maxFileSizeLimit) {
            this.setAttribute('data-max-file-size', this.jsConfig.maxFileSizeLimit);
        }

        this.#setDefaultOptions();

        this.setPlugins(this.#plugins);
    }

    /**
     * Get an attribute from the filepond input field
     * @returns string
     * @param property
     */
    getAttribute(property) {
        return this.widget.getAttribute(property);
    }

    /**
     * Set an attribute on the filepond input field
     * @returns {ContaoFilepond}
     * @param property
     * @param value
     */
    setAttribute(property, value) {
        this.widget.setAttribute(property, value);
        return this;
    }

    /**
     * Remove an attribute on the filepond input field
     * @returns {ContaoFilepond}
     * @param property
     */
    removeAttribute(property) {
        this.widget.removeAttribute(property);
        return this;
    }

    /**
     * Set the "accept" attribute on the filepond input field.
     * @param extensions
     * @returns {ContaoFilepond}
     */
    setAllowedExtensions(extensions) {
        this.widget.setAttribute('accept', extensions);
        return this;
    }

    /**
     * @returns {ContaoFilepond}
     * @param plugins
     */
    setPlugins(plugins) {
        this.#plugins = plugins;
        this.#pond.registerPlugin(
            ...this.#plugins,
        );

        return this;
    }

    /**
     *
     * @returns {FilePond}
     */
    getPond() {
        return this.#pond;
    }

    /**
     * @returns {{}}
     */
    getOptions() {
        return this.#options;
    }

    /**
     * @returns {ContaoFilepond}
     * @param options
     */
    setOptions(options) {
        this.#options = options;
        return this;
    }

    run() {
        this.#pond.create(this.widget, this.#options);
    }

    #setDefaultOptions() {
        this.#options = {
            // Add translations
            ...this.jsConfig.translations,
            maxParallelUploads: this.jsConfig.parallelUploads < 1 ? 1 : this.jsConfig.parallelUploads,
            instantUpload: true,
            allowMultiple: this.#allowMultiple,
            allowFileTypeValidation: true,

            // Do not allow upload reverts if direct upload is enabled
            allowRevert: !this.jsConfig.directUpload,
            allowRemove: !this.jsConfig.directUpload,

            // Add callbacks
            oninit: () => {
                this.#oninit();
            },
            onaddfile: (err, item) => {
                this.#onaddfile(err, item);
            },
            onaddfilestart: (file) => {
                this.#onaddfilestart(file);
            },
            onprocessfile: (err, file) => {
                this.#onprocessfile(err, file);
            },

            server: {
                process: async (fieldName, file, metadata, load, error, progress, abort) => {

                    const itemId = metadata.itemId;

                    // Event object passed into the validation pipeline.
                    // Each registered listener receives this object and performs its own validation logic.
                    //
                    // Typical validators include:
                    // - image resolution validation (min/max width & height)
                    // - maximum allowed file size validation
                    // - MIME type validation
                    // - any other custom validation rules
                    //
                    // All validators run SEQUENTIALLY and may throw an Error to stop the pipeline.
                    const event = {
                        itemId,
                        fieldName,
                        file,
                        metadata,
                        filepondOptions: this.#options,
                        jsConfig: this.jsConfig,

                        // These remain intentionally empty because success/failure
                        // is handled entirely via the try/catch around dispatch().
                        resolve() {
                        },
                        reject(err) {
                        }
                    };

                    try {
                        // ------------------------------------------------------------
                        // 1) Execute the validation pipeline (event listeners)
                        // ------------------------------------------------------------
                        //
                        // This triggers the event dispatcher.
                        // All listeners for 'contao_filepond:process_start' are executed
                        // SEQUENTIALLY in order of their priority.
                        //
                        // Each listener may:
                        // - validate image resolution
                        // - validate maximum file size
                        // - validate MIME type
                        // - perform any other checks
                        //
                        // If ANY listener throws an Error, the dispatcher stops immediately
                        // and the thrown error is caught by the catch() block below. The upload aborts.
                        //
                        await eventDispatcher.dispatch('contao_filepond:process_start', event);

                        // ------------------------------------------------------------
                        // 2) Only executed if ALL listeners resolved successfully.
                        //    If any listener threw an error, this line is skipped.
                        // ------------------------------------------------------------
                        await this.#contaoUpload(fieldName, file, metadata, load, error, progress, abort);

                    } catch (err) {

                        // ------------------------------------------------------------
                        // 3) Centralized error handling for the entire validation pipeline.
                        //
                        // Any listener that throws an Error ends up here.
                        // This ensures consistent error display for:
                        // - invalid image resolution
                        // - file too large
                        // - invalid MIME type
                        // - any other validation failure
                        // ------------------------------------------------------------
                        this.#displayError(itemId, err.message, error);
                    }
                },
                revert: (transferKey, load, error) => {
                    this.#revertUpload(transferKey, load, error)
                },

                // Not implemented yet
                fetch: null,
            },
            fileValidateTypeDetectType: (source, _type) => {
                return new Promise((resolve, _reject) => {
                    const extension = `.${source.name.split(".").pop().toLowerCase()}`;

                    resolve(extension);
                })
            },
        }

        // Allow file size validation
        this.#options.allowFileSizeValidation = true;
        this.#options.minFileSize = this.jsConfig.minFileSizeLimit;
        this.#options.maxFileSize = this.jsConfig.maxFileSizeLimit;

        // Allow client side image resizing
        if (this.jsConfig.imgResize && this.jsConfig.imgResizeBrowser && this.jsConfig.imgResizeWidth > 0 && this.jsConfig.imgResizeHeight > 0) {
            this.#options.allowImageResize = true;
            this.#options.imageResizeTargetWidth = this.jsConfig.imgResizeWidth;
            this.#options.imageResizeTargetHeight = this.jsConfig.imgResizeHeight;
            this.#options.imageResizeMode = this.jsConfig.imgResizeModeBrowser;
            this.#options.imageResizeUpscale = this.jsConfig.imgResizeUpscaleBrowser;
        }
    }

    #oninit() {
        // Add a class to the wrapper element when Filepond is initialized
        this.wrapper.classList.add('filepond--is-ready');

        // Hack: Remove the CSS marker class when the upload is done
        setInterval(() => {
            if (!this.wrapper.querySelector('.filepond--item[data-filepond-item-state="busy processing"]')) {
                this.wrapper.classList.remove('filepond--is-busy');
            }
        }, 1000);
    }

    #onaddfile(err, item) {
        item.setMetadata('itemId', item.id);
        item.setMetadata('options', this.getOptions());
    }

    #onaddfilestart(file) {
        // Add the CSS marker class when the upload starts
        this.wrapper.classList.add('filepond--is-busy');
    }

    #onprocessfile(err, file) {
    }

    /**
     * @param itemId
     * @param message
     * @param error callback function
     */
    #displayError(itemId, message, error) {

        error(message);

        const fileStatusBox = document.querySelector(
            `#filepond--item-${itemId} .filepond--file-status`
        );

        const fileStatusMainBox = fileStatusBox?.querySelector(
            `.filepond--file-status-main`
        );

        fileStatusBox?.querySelector('.filepond--contao-error')?.remove();

        if (fileStatusMainBox ?? false) {
            const errorBoxContao = document.createElement('span');
            errorBoxContao.className = 'filepond--contao-error';
            errorBoxContao.style.fontSize = '0.75rem';
            errorBoxContao.textContent = message;
            fileStatusMainBox.after(errorBoxContao);
        }
    }

    async #contaoUpload(fieldName, file, metadata, load, error, progress, abort) {
        const itemId = metadata.itemId;

        // Remove custom error boxes
        const errBoxes = document.querySelectorAll('#filepond--item-' + itemId + ' .filepond--contao-error');

        for (const errBox of errBoxes) {
            errBox.remove();
        }

        const doChunkedUpload = true === this.jsConfig.chunkUploads && this.jsConfig.chunkSize > 0 && file.size > this.jsConfig.chunkSize;

        // ---------------------------------------------------------
        // CASE 1: Normal upload (file smaller than chunk size)
        // ---------------------------------------------------------
        if (!doChunkedUpload) {

            const buffer = await file.arrayBuffer();
            const fileChecksum = await this.#sha256(buffer);

            const formData = new FormData();
            formData.append(fieldName, file, file.name);
            formData.append('REQUEST_TOKEN', this.jsConfig.csrfToken);
            formData.append('filePondItemId', itemId);
            formData.append('fileChecksum', fileChecksum);

            // Fetch does not support progress events...
            const request = new XMLHttpRequest();
            request.open('POST', window.location.href);

            request.setRequestHeader('Accept', 'application/json');
            request.setRequestHeader('name', this.name);
            request.setRequestHeader('action', 'filepond_upload');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.setRequestHeader('filePondItemId', itemId);

            request.upload.onprogress = (e) => {
                progress(e.lengthComputable, e.loaded, e.total);
            };

            request.onload = () => {
                if (request.status >= 200 && request.status < 300) {
                    const json = JSON.parse(request.response);

                    if (false === json.success ?? false) {
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
                }
            };
        }

        // ---------------------------------------------------------
        // CASE 2: Chunk upload (file larger than chunk size)
        // ---------------------------------------------------------

        let offset = 0;
        let aborted = false;
        let activeRequest = null;
        const chunkSize = this.jsConfig.chunkSize;

        const buffer = await file.arrayBuffer();
        const fileChecksum = await this.#sha256(buffer);

        const uploadFileInChunks = async () => {
            if (aborted) {
                return;
            }

            const chunk = file.slice(offset, offset + chunkSize);

            const formData = new FormData();
            formData.append('REQUEST_TOKEN', this.jsConfig.csrfToken);
            formData.append(fieldName.replace(/\[\]$/, '') + '_chunk', chunk);
            formData.append('fileChecksum', fileChecksum);
            formData.append('fileName', file.name);
            formData.append('offset', offset);
            formData.append('totalSize', file.size);

            // With fetch we use AbortController instead of XHR.abort()
            const controller = new AbortController();
            activeRequest = controller;

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'name': this.name,
                        'action': 'filepond_upload_chunk',
                        'X-Requested-With': 'XMLHttpRequest',
                        'filePondItemId': itemId
                    },
                    body: formData,
                    signal: controller.signal
                });

                if (!response.ok) {
                    error('Upload error');
                    return;
                }

                const json = await response.json();

                if (json.success === true) {
                    // everything ok
                } else if (json.success === false) {
                    this.#displayError(
                        itemId,
                        json.error ?? 'Chunk upload failed with error code 1.',
                        error
                    );
                    return;
                } else {
                    this.#displayError(
                        itemId,
                        'Chunk upload failed with error code 2.',
                        error
                    );
                    return;
                }

                // Chunk successfully uploaded: advance offset
                offset += chunk.size;

                // Chunk-based progress (we don't have per-chunk streaming progress with fetch)
                progress(true, offset, file.size);

                if (offset < file.size) {
                    // Upload next chunk
                    await uploadFileInChunks();
                } else {
                    // All chunks uploaded
                    if (json.directUpload === true) {
                        // Prevent FilePond uploading the file twice
                        load('');
                    } else {
                        load(json.transferKey);
                    }
                }

            } catch (err) {
                if (err.name === 'AbortError') {
                    // aborted intentionally
                    return;
                }
                error('Network error');
            }
        };

        uploadFileInChunks();

        return {
            abort: () => {
                aborted = true;
                if (activeRequest) {
                    activeRequest.abort();
                }
                abort();
            }
        };
    }

    async #revertUpload(transferKey, load, error) {
        try {
            const response = await fetch(window.location.href, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'text/plain',
                    'name': this.name,
                    'action': 'filepond_upload_revert',
                    'accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: transferKey
            });

            if (!response.ok) {
                throw new Error('Revert failed');
            }

            // MUST be called with no arguments
            load();

        } catch (err) {
            error('Could not revert file.');
        }
    }

    async #sha256(buffer) {
        const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
        return [...new Uint8Array(hashBuffer)]
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    }
}
