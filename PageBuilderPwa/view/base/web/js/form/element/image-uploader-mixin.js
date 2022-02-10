/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (ImageUploader) {
        return ImageUploader.extend({
            /**
             * Add file event callback triggered from media gallery
             *
             * @param {ImageUploader} imageUploader - UI Class
             * @param {Event} e
             */
            addFileFromMediaGallery: function (imageUploader, e) {
                var $buttonEl = $(e.target),
                    fileSize = $buttonEl.data('size'),
                    fileMimeType = $buttonEl.data('mime-type'),
                    filePathname = $buttonEl.val(),
                    fileBasename = filePathname.split('/').pop(),
                    imageUploaderComponent = this;

                if (fileMimeType.includes('image')) {
                    this.getImageSize(filePathname, function (width, height) {
                        imageUploaderComponent.addFile({
                            type: fileMimeType,
                            name: fileBasename,
                            size: fileSize,
                            url: filePathname,
                            imageDimensions: [width, height]
                        });
                    });
                }

                this._super(imageUploader, e);
            },

            /**
             * Handler of the file upload complete event.
             *
             * @param {Event} e
             * @param {Object} data
             */
            onFileUploaded: function (e, data) {
                var fileUrl = data.result.url,
                    parentFunction = this._super.bind(this);

                this.getImageSize(fileUrl, function (width, height) {
                    data['result']['imageDimensions'] = [width, height];
                    parentFunction(e, data);
                });
            },

            /**
             * Get file size and execute callback
             *
             * @param file
             * @param callback
             */
            getImageSize: function (file, callback) {
                var image = new Image();

                image.onload = function () {
                    callback(this.width, this.height);
                };
                image.src = file;
            }
        });
    };
});
