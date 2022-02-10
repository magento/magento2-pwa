/*eslint-disable */
/* jscs:disable */
define([
    "underscore",
    "Magento_PageBuilder/js/config",
    "Magento_PageBuilder/js/utils/image",
    "Magento_PageBuilder/js/utils/object",
    "Magento_PageBuilder/js/utils/url"
], function (_underscore, _config, _image, _object, _url) {
  /**
   * Copyright © Magento, Inc. All rights reserved.
   * See COPYING.txt for license details.
   */

  return function () {
      "use strict";

      function ImageDesktopDimensions() {
      }

      var _proto = ImageDesktopDimensions.prototype;

      /**
       * Convert value to internal format
       *
       * @param value string
       * @returns {string | object}
       */
      _proto.fromDom = function fromDom(value) {
          if (!value) {
              return '';
          }

          return value;
      }

      /**
       * Convert value to knockout format
       *
       * @param {string} name
       * @param {DataObject} data
       * @returns {string}
       */
      _proto.toDom = function toDom(name, data) {
          var desktopImage = data['image'];
          var currentValue = data[name] || null;
          var currentDimensions;
          try {
              currentDimensions = currentValue ? JSON.parse(currentValue) : {};
          } catch (error) {
              console.error(error);
              currentDimensions = {};
          }
          var ImageDimensions = Object.assign({}, currentDimensions);

          if (!_underscore.isUndefined(desktopImage)
              && desktopImage
              && !_underscore.isUndefined(desktopImage[0])
          ) {
              // Add dimensions of newly selected image
              if (!_underscore.isUndefined(desktopImage[0].imageDimensions) && desktopImage[0].imageDimensions) {
                  ImageDimensions = {
                      height: desktopImage[0].imageDimensions[1],
                      width: desktopImage[0].imageDimensions[0],
                      ratio: Math.round((desktopImage[0].imageDimensions[1] / desktopImage[0].imageDimensions[0] + Number.EPSILON) * 100) / 100
                  };
              }
          }
          return Object.keys(ImageDimensions).length > 0 ? JSON.stringify(ImageDimensions) : currentValue;
      };

      return ImageDesktopDimensions;
  }();
});
