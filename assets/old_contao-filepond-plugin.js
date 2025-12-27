class ContaoFilepondPlugin {
    wrapper = null;
    widget = null;
    name = null;
    jsConfig = null;
    #allowMultiple = false;
    #pond = null;
    #options = {};
    #plugins = [
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginFileValidateSize,
        FilePondPluginImageEdit,
        FilePondPluginFileValidateType,
        FilePondPluginImageValidateSize,
    ];

    /**
     *
     * @param widget file input element
     * @param name
     * @param jsConfig
     * @param pond
     */
    constructor(widget, name, jsConfig, pond) {
        this.wrapper = widget.closest('.filepond-wrapper');
        this.widget = widget;
        this.name = name;
        this.jsConfig = jsConfig;
        this.#pond = pond;

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
        if (this.jsConfig.minSizeLimit) {
            this.setAttribute('data-min-file-size', this.jsConfig.minSizeLimit);
        }

        // Set the data-max-file-size attribute on the filepond input field.
        if (this.jsConfig.maxSizeLimit) {
            this.setAttribute('data-max-file-size', this.jsConfig.maxSizeLimit);
        }

        this.setPlugins(this.#plugins);

        this.#setDefaultOptions();
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
     * @returns {ContaoFilepondPlugin}
     * @param property
     * @param value
     */
    setAttribute(property, value) {
        this.widget.setAttribute(property, value);
        return this;
    }

    /**
     * Remove an attribute on the filepond input field
     * @returns {ContaoFilepondPlugin}
     * @param property
     */
    removeAttribute(property) {
        this.widget.removeAttribute(property);
        return this;
    }

    /**
     * Set the "accept" attribute on the filepond input field.
     * @param extensions
     * @returns {ContaoFilepondPlugin}
     */
    setAllowedExtensions(extensions) {
        this.widget.setAttribute('accept', extensions);
        return this;
    }

    /**
     * @returns {ContaoFilepondPlugin}
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
     * @returns {ContaoFilepondPlugin}
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
            maxParallelUploads: this.jsConfig.maxConnections,
            instantUpload: true,
            allowMultiple: this.#allowMultiple,
            allowFileTypeValidation: true,

            // Add callbacks
            oninit: () => {
                this.#oninit();
            },
            onaddfilestart: (file) => {
                this.#onaddfilestart(file);
            },
            onprocessfile: (err, file) => {
                this.#onprocessfile(err, file);
            },
            onaddfile: (err, item) => {
                this.#onaddfile(err, item);
            },

            server: {
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    // Get the item id from metadata (added in onaddfile())
                    const itemId = metadata.itemId;

                    // Remove the injected custom error box if there is one...
                    const errBoxes = document.querySelectorAll('#filepond--item-' + itemId + ' .filepond--contao-error');
                    for (const errBox of errBoxes) {
                        errBox.remove();
                    }

                    // fieldName is the name of the input field
                    // file is the actual file object to send
                    const formData = new FormData();
                    formData.append(fieldName, file, file.name);
                    formData.append('REQUEST_TOKEN', this.jsConfig.csrfToken);
                    formData.append('action', 'filepond_upload');

                    const request = new XMLHttpRequest();

                    // Open post request
                    request.open('POST', window.location.href);

                    // Set headers
                    request.setRequestHeader('Accept', 'application/json');
                    request.setRequestHeader('name', this.name);
                    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    request.setRequestHeader('filePondItemId', itemId);

                    // Should call the progress method to update the progress to 100% before calling load
                    // Setting computable to false switches the loading indicator to infinite mode
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };

                    // Should call the load method when done and pass the returned server file id
                    // this server file id is then used later on when reverting or restoring a file
                    // so your server knows which file to return without exposing that info to the client
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            // the load method accepts either a string (id) or an object
                            const jsonResponse = JSON.parse(request.response);

                            if (jsonResponse.success === true) {
                                load(jsonResponse.transferKey);
                            } else if (jsonResponse.error) {
                                error(jsonResponse.error)
                            } else {
                                error('There has been an error!');
                            }
                        } else {
                            // Can call the error method if something is wrong, should exit after
                            error('There has been an error!');
                        }
                    };

                    request.send(formData);

                    // Should expose an abort method so the request can be cancelled
                    return {
                        abort: () => {
                            // This function is entered if the user has tapped the cancel button
                            request.abort();

                            // Let FilePond know the request has been cancelled
                            abort();
                        },
                    };
                },

                fetch: null,
                revert: null,
            },
            fileValidateTypeDetectType: (source, _type) => {
                return new Promise((resolve, _reject) => {
                    const extension = `.${source.name.split(".").pop().toLowerCase()}`;
                    resolve(extension);
                })
            },
        }

        // Allow image size validation
        this.#options.allowImageValidateSize = true;

        if (this.jsConfig.maxImageWidth) {
            this.#options.imageValidateSizeMaxWidth = this.jsConfig.maxImageWidth;
        }

        if (this.jsConfig.maxImageHeight) {
            this.#options.imageValidateSizeMaxHeight = this.jsConfig.maxImageHeight;
        }

        // Allow file size validation
        this.#options.allowFileSizeValidation = true;

        if (this.jsConfig.minSizeLimit) {
            this.#options.minFileSize = this.jsConfig.minSizeLimit;
        }

        if (this.jsConfig.maxSizeLimit) {
            this.#options.maxFileSize = this.jsConfig.maxSizeLimit;
        }

        // Allow client side image resizing
        if (this.jsConfig.allowImageResize) {
            this.#pond.registerPlugin(
                FilePondPluginImageResize,
                FilePondPluginImageTransform,
            );

            this.#options.allowImageResize = true;
            this.#options.imageResizeTargetWidth = this.jsConfig.imageResizeTargetWidth;
            this.#options.imageResizeTargetHeight = this.jsConfig.imageResizeTargetHeight;
            this.#options.imageResizeMode = this.jsConfig.imageResizeMode;
            this.#options.imageResizeUpscale = this.jsConfig.imageResizeUpscale;
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
    }

    #onaddfilestart(file) {
        // Add the CSS marker class when the upload starts
        this.wrapper.classList.add('filepond--is-busy');
    }

    #onprocessfile(err, file) {
        // Inject error message from server to the list item
        if (err?.body !== undefined && err?.body !== '') {
            const error = err.body;

            const itemId = file.id;
            const fileStatusMain = document.querySelector('#filepond--item-' + itemId + ' .filepond--file-status-main');
            if (fileStatusMain) {
                const errorBox = document.createElement('span');
                errorBox.setAttribute('class', 'filepond--contao-error');
                errorBox.setAttribute('style', 'font-size: 0.75rem')
                errorBox.innerText = error;
                fileStatusMain.parentNode.insertBefore(errorBox, fileStatusMain.nextSibling);
            }
        }
    }
}
