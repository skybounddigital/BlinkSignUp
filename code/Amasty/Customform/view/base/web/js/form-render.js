// Element.remove() polyfill
/* eslint-disable one-var, vars-on-top, max-depth, no-shadow */

define([
    'jquery',
    'mage/url',
    'mage/translate',
    'mage/calendar',
    'Magento_Ui/js/modal/modal'
], function ($, urlBuilder) {
    'use strict';

    if (!('remove' in Element.prototype)) {
        Element.prototype.remove = function () {
            if (this.parentNode) {
                this.parentNode.removeChild(this);
            }
        };
    }

    // Event polyfill
    if (typeof Event !== 'function') {
        (function () {
            window.Event = function (evt) {
                var event = document.createEvent('Event');

                event.initEvent(evt, true, true);

                return event;
            };
        })();
    }

    // Object.assign polyfill
    if (typeof Object.assign != 'function') {
        Object.assign = function (target) {
            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }

            target = Object(target);

            for (var index = 1; index < arguments.length; index++) {
                var source = arguments[index];

                if (source !== null) {
                    for (var key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            target[key] = source[key];
                        }
                    }
                }
            }

            return target;
        };
    }

    'use strict';


    (function ($) {
        var Toggle = function Toggle(element, options) {
            var defaults = {
                theme: 'fresh',
                messages: {
                    off: 'Off',
                    on: 'On'
                }
            },
            opts = $.extend(defaults, options),
                $kcToggle = $('<div class="kc-toggle"></div>').insertAfter(element).append(element);

            $kcToggle.toggleClass('on', element.is(':checked'));

            var kctOn = '<div class="kct-on">' + opts.messages.on + '</div>',
                kctOff = '<div class="kct-off">' + opts.messages.off + '</div>',
                kctHandle = '<div class="kct-handle"></div>',
                kctInner = '<div class="kct-inner">' + kctOn + kctHandle + kctOff + '</div>';

            $kcToggle.append(kctInner);

            $kcToggle.click(function () {
                element.attr('checked', !element.attr('checked'));
                $(this).toggleClass('on');
            });
        };

        $.fn.kcToggle = function (options) {
            var toggle = this;

            return toggle.each(function () {
                var element = $(this),
                    kcToggle;

                if (element.data('kcToggle')) {
                    return;
                }

                kcToggle = new Toggle(element, options);
                element.data('kcToggle', kcToggle);
            });
        };
    })($);

    'use strict';

    // eslint-disable-next-line newline-after-var
    var _typeof = typeof Symbol === 'function' && typeof Symbol.iterator === 'symbol' ? function (obj) {
        return typeof obj;
    } : function (obj) {
        return obj && typeof Symbol === 'function'
            && obj.constructor === Symbol
            && obj !== Symbol.prototype ? 'symbol' : typeof obj;
    },

    fbUtils = {};
    fbUtils.amProgressId = 'form_submit_loading';
    fbUtils.amImageContainerId = 'loading_image_container';
    fbUtils.formOptions = [];

    // cleaner syntax for testing indexOf element
    fbUtils.inArray = function (needle, haystack) {
        return haystack.indexOf(needle) !== -1;
    };

    /**
     * Remove null or undefined values
     * @param  {Object} attrs {attrName: attrValue}
     * @return {Object}       Object trimmed of null or undefined values
     */
    fbUtils.trimObj = function (attrs) {
        var xmlRemove = [null, undefined, '', false, 'false'];

        for (var attr in attrs) {
            if (fbUtils.inArray(attrs[attr], xmlRemove)) {
                delete attrs[attr];
            } else if (Array.isArray(attrs[attr])) {
                if (!attrs[attr].length) {
                    delete attrs[attr];
                }
            }
        }

        return attrs;
    };

    /**
     * Test if attribute is a valid HTML attribute
     * @param  {String} attr
     * @return {Boolean}
     */
    fbUtils.validAttr = function (attr) {
        var invalid = [
            'values', 'enableOther', 'other', 'label',
            'validation_fields', 'subtype', 'dependency',
            'map_position'
        ];

        return !fbUtils.inArray(attr, invalid);
    };

    /**
     * Convert an attrs object into a string
     *
     * @param  {Object} attrs object of attributes for markup
     * @return {string}
     */
    fbUtils.attrString = function (attrs) {
        var attributes = [];

        for (var attr in attrs) {
            if (attrs.hasOwnProperty(attr) && fbUtils.validAttr(attr)) {
                if (attr === 'placeholder') {
                    attributes.push(attr + '="' + attrs[attr] + '"');
                } else {
                    attr = fbUtils.safeAttr(attr, attrs[attr]);
                    attributes.push(attr.name + attr.value);
                }
            }
        }

        return attributes.join(' ');
    };

    /**
     * Convert attributes to markup safe strings
     * @param  {String} name  attribute name
     * @param  {String} value attribute value
     * @return {Object}       {attrName: attrValue}
     */
    fbUtils.safeAttr = function (name, value) {
        var valString = void 0;

        name = fbUtils.safeAttrName(name);

        if (value) {
            if (Array.isArray(value)) {
                valString = fbUtils.escapeAttr(value.join(' '));
            } else {
                if (typeof value === 'boolean') {
                    value = value.toString();
                }

                value = value.toString();

                if (['regexp', 'data-validate'].includes(name)) {
                    valString = fbUtils.escapeAttr(value);
                } else {
                    valString = fbUtils.escapeAttr(value.replace(',', ' ').trim());
                }
            }
        }

        value = value ? '="' + valString + '"' : '';

        return {
            name: name,
            value: value
        };
    };

    fbUtils.safeAttrName = function (name) {
        var safeAttr = {
            className: 'class'
        };

        return safeAttr[name] || fbUtils.hyphenCase(name);
    };

    /**
     * Convert strings into lowercase-hyphen
     *
     * @param  {String} str
     * @return {String}
     */
    fbUtils.hyphenCase = function (str) {
        str = str.replace(/[^\w\s\-]/gi, '');
        str = str.replace(/([A-Z])/g, function ($1) {
            return '-' + $1.toLowerCase();
        });

        return str.replace(/\s/g, '-').replace(/^-+/g, '');
    };

    /**
     * convert a hyphenated string to camelCase
     * @param  {String} str
     * @return {String}
     */
    fbUtils.camelCase = function (str) {
        return str.replace(/-([a-z])/g, function (m, w) {
            return w.toUpperCase();
        });
    };

    /**
     * Generate markup wrapper where needed
     *
     * @param  {string} tag
     * @return {String}
     */
    fbUtils.markup = function (tag) {
        var content = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '',
            attrs = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
            contentType = void 0,
            field = document.createElement(tag),

            getContentType = function getContentType(content) {
                return Array.isArray(content) ?
                    'array' : typeof content === 'undefined' ? 'undefined' : _typeof(content);
            },

            appendContent = {
                string: function string(content) {
                    field.innerHTML = content;
                },
                object: function object(content) {
                    return field.appendChild(content);
                },
                array: function array(content) {
                    for (var i = 0; i < content.length; i++) {
                        contentType = getContentType(content[i]);
                        appendContent[contentType](content[i]);
                    }
                }
            };

        for (var attr in attrs) {
            if (attrs.hasOwnProperty(attr)) {
                var name = fbUtils.safeAttrName(attr);

                field.setAttribute(name, attrs[attr]);
            }
        }

        contentType = getContentType(content);

        if (content) {
            appendContent[contentType].call(this, content);
        }

        return field;
    };

    /**
     * Convert html element attributes to key/value object
     * @return {Object} ex: {attrName: attrValue}
     * @param {object} elem
     */
    fbUtils.parseAttrs = function (elem) {
        var attrs = elem.attributes,
            data = {};

        fbUtils.forEach(attrs, function (attr) {
            var attrVal = attrs[attr].value;

            if (attrVal.match(/false|true/g)) {
                attrVal = attrVal === 'true';
            } else if (attrVal.match(/undefined/g)) {
                attrVal = undefined;
            }

            if (attrVal) {
                data[attrs[attr].name] = attrVal;
            }
        });

        return data;
    };

    /**
     * Convert field options to optionData
     * @return {Array} optionData array
     * @param {object} field
     */
    fbUtils.parseOptions = function (field) {
        var options = field.getElementsByTagName('option'),
            optionData = {},
            data = [];

        if (options.length) {
            for (var i = 0; i < options.length; i++) {
                optionData = fbUtils.parseAttrs(options[i]);
                optionData.label = options[i].textContent;
                data.push(optionData);
            }
        }

        return data;
    };

    /**
     * Parse XML formData
     * @param  {String} xmlString
     * @return {Array} formData array
     */
    fbUtils.parseXML = function (xmlString) {
        var parser = new window.DOMParser(),
            xml = parser.parseFromString(xmlString, 'text/xml'),
            formData = [];

        if (xml) {
            var fields = xml.getElementsByTagName('field');

            for (var i = 0; i < fields.length; i++) {
                var fieldData = fbUtils.parseAttrs(fields[i]);

                if (fields[i].children.length) {
                    fieldData.values = fbUtils.parseOptions(fields[i]);
                }

                formData.push(fieldData);
            }
        }

        return formData;
    };

    /**
     * Escape markup, so it can be displayed rather than rendered
     * @param  {String} html markup
     * @return {String} escaped html
     */
    fbUtils.escapeHtml = function (html) {
        var escapeElement = document.createElement('textarea');

        escapeElement.textContent = html;

        return escapeElement.innerHTML;
    };

    // Escape an attribute
    fbUtils.escapeAttr = function (str) {
        var match = {
            '"': '&quot;',
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;'
        };

        function replaceTag(tag) {
            return match[tag] || tag;
        }

        return typeof str === 'string' ? str.replace(/["&<>]/g, replaceTag) : str;
    };

    // Escape attributes
    fbUtils.escapeAttrs = function (attrs) {
        for (var attr in attrs) {
            if (attrs.hasOwnProperty(attr)) {
                attrs[attr] = fbUtils.escapeAttr(attrs[attr]);
            }
        }

        return attrs;
    };

    // forEach that can be used on nodeList
    fbUtils.forEach = function (array, callback, scope) {
        for (var i = 0; i < array.length; i++) {
            callback.call(scope, i, array[i]); // passes back stuff we need
        }
    };

    /**
     * Remove duplicates from an array of elements
     * @return {Array} array with only unique values
     * @param {array} array
     */
    fbUtils.unique = function (array) {
        return array.filter(function (elem, pos, arr) {
            return arr.indexOf(elem) === pos;
        });
    };

    /**
     * Generate preview markup
     * @param  {object} fieldData
     * @param {object} opts
     * @return {string} preview markup for field
     */
    fbUtils.fieldRender = function (fieldData, opts) {
        var preview = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false,
            utils = fbUtils,
            fieldMarkup = '',
            fieldLabel = '',
            optionsMarkup = '',
            fieldLabelText = fieldData.label || '',
            fieldDesc = fieldData.description || '',
            fieldRequired = '',
            fieldOptions = fieldData.values;

        fieldData.name = preview ? fieldData.name + '-preview' : fieldData.name;
        fieldData.id = fieldData.name;

        if (
            utils.inArray(
                fieldData.type,
                ['textinput', 'number', 'file', 'dropdown', 'listbox', 'textarea']
            ) && fieldData.className.indexOf('form-control') === -1
        ) {
            fieldData.className += ' form-control';
        }

        if (fieldData.type === 'listbox') {
            fieldData.multiple = true;
        }

        fieldData.name = fieldData.multiple ? fieldData.name + '[]' : fieldData.name;
        fieldData.type = fieldData.subtype || fieldData.type;

        var withoutLabel = false,
            ratingClass = '',
            withBr = true,
            withoutMainLabel = false;

        switch (fieldData.type) {
            case 'textinput':
                fieldData.type = 'text';
                break;
            case 'textarea':
                fieldData.type = 'textarea';
                break;
            case 'hidden':
                fieldData.type = 'hidden';
                withoutLabel = true;
                break;
            case 'text':
                fieldData.type = 'div';
                withoutLabel = true;
                break;
            case 'hone':
                fieldData.type = 'h1';
                withoutLabel = true;
                break;
            case 'htwo':
                fieldData.type = 'h2';
                withoutLabel = true;
                break;
            case 'hthree':
                fieldData.type = 'h3';
                withoutLabel = true;
                break;
            case 'number':
                fieldData.type = 'number';
                break;
            case 'rating':
                ratingClass = 'amform-rating-container ';
                withBr = false;
            // eslint-disable-next-line no-fallthrough
            case 'radiotwo':
                withBr = false;
            // eslint-disable-next-line no-fallthrough
            case 'radio':
                fieldData.type = 'radio-group';
                break;
            case 'checkboxtwo':
                withBr = false;
            // eslint-disable-next-line no-fallthrough
            case 'checkbox':
                fieldData.type = 'checkbox-group';
                break;
            case 'single-checkbox':
                fieldData.type = 'checkbox';
                withoutMainLabel = true;
                break;
            case 'listbox':
                fieldData.type = 'select';
                break;
            case 'dropdown':
                fieldData.type = 'select';
                break;
            case 'daterange':
                fieldData.type = 'date';
                break;
            case 'datetime':
                fieldData.type = 'datetime-local';
                break;
            case 'wysiwyg':
                fieldData.type = 'wysiwyg';
                break;
        }

        fieldData['data-amcform-js'] = fieldData.id;

        if (fieldData.required) {
            delete fieldData.required;
            fieldData['aria-required'] = 'true';
            fieldData.className += ' required-entry';
            fieldRequired = '<span class="required">*</span>';
        }

        if (!withoutLabel) {
            if (fieldDesc) {
                fieldDesc = '<span class="tooltip-element" tooltip="' + fieldDesc + '">?</span>';
            }

            fieldLabel = '<label for="' + fieldData.id + '" class="fb-' + fieldData.type + '-label label">'
                + fieldLabelText + ' ' + fieldRequired + ' ' + fieldDesc
                + '</label>';
        }

        var fieldLabelVal = fieldData.label;

        delete fieldData.label;
        delete fieldData.description;

        var fieldDataString = fbUtils.attrString(fieldData);

        switch (fieldData.type) {
            case 'wysiwyg':
                fieldMarkup = '<div ' + fieldDataString + '>' + (fieldData.value || '') + '</div>';
                break;
            case 'textarea':
            case 'rich-text':
                delete fieldData.type;

                var fieldVal = fieldData.value || '';

                fieldMarkup = '<textarea ' + fieldDataString + '>' + fieldVal + '</textarea>';
                break;
            case 'select':
                var optionAttrsString;

                fieldData.type = fieldData.type.replace('-group', '');

                if (fieldOptions) {
                    if (fieldData.placeholder) {
                        optionsMarkup += '<option disabled selected>' + fieldData.placeholder + '</option>';
                    }

                    for (var i = 0; i < fieldOptions.length; i++) {
                        if (!fieldOptions[i].selected || fieldData.placeholder) {
                            delete fieldOptions[i].selected;
                        }

                        if (!fieldOptions[i].label) {
                            fieldOptions[i].label = '';
                        }

                        optionAttrsString = fbUtils.attrString(fieldOptions[i]);
                        optionsMarkup += '<option ' + optionAttrsString + '>' + fieldOptions[i].label + '</option>';
                    }
                }

                fieldMarkup = '<select ' + fieldDataString + '>' + optionsMarkup + '</select>';
                break;
            case 'checkbox-group':
                if (fieldData.toggle === '1') {
                    setTimeout(function () {
                        $('[id^="' + fieldData.id + '"]').kcToggle();
                    }, 100);
                }
            // eslint-disable-next-line no-fallthrough
            case 'rating':
            case 'radio-group':
                var optionAttrs = void 0;

                fieldData.type = fieldData.type.replace('-group', '');

                if (fieldData.type === 'checkbox') {
                    fieldData.name += '[]';
                }

                if (ratingClass) {
                    fieldOptions = fieldOptions.reverse();
                }

                if (fieldOptions) {
                    var _optionAttrsString = void 0;

                    for (var _i = 0; _i < fieldOptions.length; _i++) {
                        optionAttrs = Object.assign({value: '', label: ''}, fieldData, fieldOptions[_i]);

                        if (optionAttrs.selected) {
                            delete optionAttrs.selected;
                            optionAttrs.checked = null;
                        }

                        optionAttrs.id = fieldData.id + '-' + _i;
                        _optionAttrsString = fbUtils.attrString(optionAttrs);

                        var classNameLabel = ' class="amform-versiontwo-label"',
                            delimeter = '';

                        if (withBr) {
                            delimeter = '<br>';
                            // eslint-disable-next-line no-use-before-define
                            className = '';
                        } else if (!ratingClass) {
                            optionsMarkup += '<div class="amform-groupv2">';
                            delimeter = '</div>';
                        }

                        var labelText = ratingClass ? '' : optionAttrs.label;

                        optionsMarkup += '<input ' + _optionAttrsString + ' /' + '> <label for="'
                            + optionAttrs.id + '" ' + classNameLabel + '>' + labelText + '</label>' + delimeter;
                    }

                    if (fieldData.other) {
                        var otherOptionAttrs = {
                            id: fieldData.id + '-' + 'other',
                            className: fieldData.className + ' other-option',
                            onclick: 'fbUtils.otherOptionCB(\'' + fieldData.id + '-other\')'
                        };

                        _optionAttrsString = fbUtils.attrString(Object.assign({}, fieldData, otherOptionAttrs));

                        optionsMarkup += '<input ' + _optionAttrsString + ' /' + '>'
                            + '<label for="' + otherOptionAttrs.id + '">'
                            + opts.messages.other + '</label> <input type="text" name="'
                            + fieldData.name + '" id="' + otherOptionAttrs.id
                            + '-value" style="display:none;"' + '/' + '>';
                    }
                }
                fieldMarkup = '<div class="' + ratingClass + fieldData.type + '-group">' + optionsMarkup + '</div>';
                break;
            case 'text':
            case 'password':
            case 'email':
            case 'number':
            case 'file':
            case 'hidden':
            case 'date':
            case 'time':
            case 'datetime-local':
            case 'tel':
            case 'autocomplete':
                fieldMarkup = ' <input ' + fieldDataString + '>';
                break;
            case 'color':
                fieldMarkup = ' <input ' + fieldDataString + '> ' + opts.messages.selectColor;
                break;
            case 'button':
            case 'submit':
                fieldMarkup = '<button ' + fieldDataString + '>' + fieldLabelVal + '</button>';
                break;
            case 'checkbox':
                fieldMarkup = '<input ' + fieldDataString + '> ' + fieldLabel;
            // eslint-disable-next-line no-fallthrough
            case 'checkboxtwo':
                if (fieldData.toggle) {
                    setTimeout(function () {
                        $('[id^="' + fieldData.id + '"]').kcToggle();
                    }, 100);
                }
                break;
            case 'googlemap':
                fieldMarkup = ' <input ' + fieldDataString + '><div class="map"></div>';
                break;
            default:
                fieldMarkup = '<' + fieldData.type + ' ' + fieldDataString + '>'
                + fieldLabelVal + '</' + fieldData.type + '>';
        }

        fieldMarkup = '<div class="control">' + fieldMarkup + '</div>';
        if (!withoutMainLabel) {
            fieldMarkup = fieldLabel + fieldMarkup;
        }

        if (fieldData.type !== 'hidden') {
            var className = fieldData.id ?
                'field fb-' + fieldData.type + ' form-group field-' + fieldData.id.replace(/\)|\(/g, '-') :
                '';

            className += fieldData.dependency ? ' am-customform-depend' : '';

            if (fieldData.layout) {
                className += ' amform-layout-' + fieldData.layout;
            }

            fieldMarkup = fbUtils.markup('div', fieldMarkup, {
                className: className
            });
        } else {
            fieldMarkup = fbUtils.markup('input', null, fieldData);
        }

        switch (fieldData.type) {
            case 'googlemap':
                var position = null, zoom = 1;

                if (fieldData.map_position.lat
                    && fieldData.map_position.lng
                    && typeof google !== 'undefined'
                ) {
                    position = new google.maps.LatLng(
                        fieldData.map_position.lat,
                        fieldData.map_position.lng
                    );
                    zoom = fieldData.zoom;
                }

                $(fieldMarkup).amGoogleMap({
                    'position': position,
                    'zoom': zoom,
                    'styles': fieldData.style
                });
                break;
            case 'date':
                var dateInput = $(fieldMarkup).find('input');

                dateInput.removeAttr('type');
                dateInput.attr('readonly', 'readonly');
                dateInput.datepicker({
                    showOn: 'both',
                    changeYear: true,
                    yearRange: '1900:2100',
                    autoSize: true,
                    dateFormat: opts.dateFormat
                });

                if (fieldData.value) {
                    dateInput.datepicker('setDate', fieldData.value);
                }

                dateInput.attr('placeholder', opts.placeholder);
                dateInput.attr('size', 15);
                $(fieldMarkup).find('button').html('');

                var datepicker = $('#ui-datepicker-div');

                if (!datepicker.hasClass('am-picker-year')) {
                    datepicker.addClass('am-picker-year');
                }
                break;
        }

        return fieldMarkup;
    };

    /**
     * Callback for other option.
     * Toggles the hidden text area for "other" option.
     * @param  {String} otherId id of the "other" option input
     */
    fbUtils.otherOptionCB = function (otherId) {
        var otherInput = document.getElementById(otherId),
            otherInputValue = document.getElementById(otherId + '-value');

        if (otherInput.checked) {
            otherInput.style.display = 'none';
            otherInputValue.style.display = 'inline-block';
        } else {
            otherInput.style.display = 'inline-block';
            otherInputValue.style.display = 'none';
        }
    };

    /**
     * Capitalizes a string
     * @param  {String} str uncapitalized string
     * @return {String} str capitalized string
     */
    fbUtils.capitalize = function (str) {
        return str.replace(/\b\w/g, function (m) {
            return m.toUpperCase();
        });
    };

    /**
     * Check if need show hidden fields
     * @param {FormRenderFn} formRender
     * @param {object} $
     */
    fbUtils.updateDependency = function (formRender, $) {
        $.each(formRender.element.find('.am-customform-depend'), function ($, index, elem) {
            var name = $(elem).find(
                '.control input,' +
                '.control [type="div"],' +
                '.control [type="h1"],' +
                '.control [type="h2"],' +
                '.control [type="h3"],' +
                '.control [type="wysiwyg"],' +
                '.form-control'
            ).first().attr('name');

            if (this.dependencyMap[name]) {
                fbUtils.isFieldShowed(elem, name, this.dependencyMap);
            }
        }.bind(formRender, $));
    };

    fbUtils.isFieldShowed = function (checkedField, elementName, dependencyMap) {
        var orFields = [],
            orFieldsType = ['dropdown', 'radio', 'radiotwo'],
            hiddenField = $(checkedField),
            hiddenInput = hiddenField.find('input, textarea, select'),
            validElems = 0;

        $.each(dependencyMap[elementName], function (index, elem) {
            var dependencyElem = null;

            // check current dependency, only if dependency element showed
            if (dependencyMap[elem.field] &&
                !fbUtils.isFieldShowed($('.field-' + elem.field.replace(/\)|\(/g, '-')), elem.field, dependencyMap)
            ) {
                return false;
            }

            // find element and check value
            // if ok - increment validElems
            // dropdown&radio always increment validElems , checked for valid by orFieldsValid
            switch (elem.type) {
                case 'dropdown':
                    dependencyElem = $('select[name="' + elem.field + '"]');
                    validElems++;

                    if (typeof orFields[elem.field] == 'undefined') {
                        orFields[elem.field] = false;
                    }

                    break;
                case 'listbox':
                    if ($('select[name="' + elem.field + '[]"]').val().indexOf(elem.value) !== -1) {
                        validElems++;
                    }
                    break;
                case 'checkbox':
                case 'checkboxtwo':
                    var getCheckboxOptions = function (index, elem) {
                        return elem.value;
                    };

                    dependencyElem = $('[name="' + elem.field + '[]"]:checked');

                    if (dependencyElem.map(getCheckboxOptions).toArray().indexOf(elem.value) !== -1) {
                        validElems++;
                    }

                    dependencyElem = null;
                    break;
                case 'radio':
                case 'radiotwo':
                    validElems++;
                    dependencyElem = $('input[name="' + elem.field + '"]:checked');

                    if (typeof orFields[elem.field] == 'undefined') {
                        orFields[elem.field] = false;
                    }

                    break;
                default:
                    validElems++;
            }

            if (dependencyElem && dependencyElem.val() === elem.value) {
                if (orFieldsType.indexOf(elem.type) !== -1) {
                    orFields[elem.field] = true;
                } else {
                    validElems++;
                }
            }
        });

        var orFieldsValid = fbUtils.getEntries(orFields).map(function (pair) {
            return pair[1];
        }).indexOf(false) === -1;

        // validElems - must be count all dependencies of element
        // dropdown&radio validated with orFieldsValid - one suitable option must selected
        if (dependencyMap[elementName].length === validElems && orFieldsValid) {
            hiddenInput
                .removeClass('amcform-hidden-field')
                .removeAttr('disabled');
            hiddenField.show();

            return true;
        }

        hiddenInput
            .addClass('amcform-hidden-field')
            .attr('disabled', true);

        hiddenField.hide();

        return false;
    };

    fbUtils.getEntries = function (obj) {
        return Object.entries
            ? Object.entries(obj)
            : Object.keys(obj).map(function (key) {
                return [key, obj[key]];
            });
    };

    fbUtils.showAnimation = function (loaderImage) {
        var progress = $('<div></div>', {id: this.amProgressId}),
            container = $('<div></div>', {id: this.amImageContainerId}),
            img = $('<img>'),
            width;

        container.appendTo(progress);

        img.attr('src', loaderImage);
        img.appendTo(container);
        container.width('150px');
        width = container.width();
        width = '-' + width / 2 + 'px';
        container.css('margin-left', width);
        progress.hide().appendTo($('body')).fadeIn();
    };

    fbUtils.hideAnimation = function () {
        var element = $('#' + this.amProgressId);

        if (element.length) {
            element.fadeOut(function () {
                $(this).remove();
            });
        }
    };

    fbUtils.submitForm = function (event) {
        var form = this;

        event.preventDefault();

        if (form.valid()) {
            form.find('[type="submit"]').addClass('disabled');
            if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                form.off('submit');
                form.submit();
            } else {
                var formId = form.attr('id').match(/\d+/);

                $.ajax({
                    url: form.attr('action'),
                    data: form.serialize(),
                    type: 'post',
                    dataType: 'json',

                    beforeSend: function () {
                        fbUtils.showAnimation(fbUtils.formOptions[formId].src_image_progress);
                    },

                    error: function () {
                        fbUtils.hideAnimation();

                        $('html, body').animate({
                            scrollTop: $('body').offset().top
                        }, 500);

                        form.find('[type="submit"]').removeClass('disabled');
                    },

                    success: function (response) {
                        form.closest('.amcform-popup-block').removeClass('-active');
                        form.find('[type="submit"]').removeClass('disabled');
                        fbUtils.hideAnimation();

                        if (response.result === 'success') {
                            var renderedForm = form.find('.insert-container'),
                                opts = fbUtils.formOptions[formId],
                                gdpr = form.find('[name="gdpr"]');

                            if (formId) {
                                formId = parseInt(formId, 10);
                                renderedForm.empty();
                                renderedForm.formRender(formId, opts, form.find('[name="is_survey"]').attr('value'));

                                if (gdpr.length > 0) {
                                    gdpr.removeAttr('checked');
                                }

                                if (form.parent().hasClass('amform-popup')) {
                                    form.parent().hide();
                                } else if (form.hasClass('amhideprice-form')) {
                                    $.fancyambox.close();
                                }
                            }

                            $(document).trigger('amcform-init-multipage', [renderedForm]);
                        }
                        window.scrollTo(0, 0);
                    }
                });
            }
        }
    };

    'use strict';

    function FormRenderFn(formId, options, element) {
        var utils = fbUtils;

        fbUtils.formOptions[formId] = options;

        var formRender = this,
            defaults = {
                destroyTemplate: true, // @todo
                container: false,
                dataType: 'xml',
                formData: false,
                messages: {
                    formRendered: 'Form Rendered',
                    noFormData: 'No form data.',
                    other: 'Other',
                    selectColor: 'Select Color'
                },
                onRender: function onRender() {
                    $('.amform-hide-formload').removeClass('amform-hide-formload');
                },
                render: true,
                notify: {
                    error: function error(message) {
                        return console.error(message);
                    },
                    success: function success(message) {
                        return console.log(message);
                    },
                    warning: function warning(message) {
                        return console.warn(message);
                    }
                }
            },
            opts = $.extend(true, defaults, options);

        (function () {
            if (!opts.formData) {
                return false;
            }

            var setData = {
                xml: function xml(formData) {
                    return utils.parseXML(formData);
                },
                json: function json(formData) {
                    return window.JSON.parse(formData);
                }
            };

            opts.formData = setData[opts.dataType](opts.formData) || false;
        })();

        /**
         * Extend Element prototype to allow us to append fields
         *
         * @param  {Object} fields Node elements
         */
        Element.prototype.appendFormFields = function (fields) {
            var element = this;

            fields.forEach(function (field) {
                return element.appendChild(field);
            });
        };

        /**
         * Extend Element prototype to remove content
         */
        Element.prototype.emptyContainer = function () {
            var element = this;

            while (element.lastChild) {
                element.removeChild(element.lastChild);
            }
        };

        var runCallbacks = function runCallbacks() {
            if (opts.onRender) {
                opts.onRender();
            }
        },

         santizeField = function santizeField(field) {
            var sanitizedField = Object.assign({}, field);

            sanitizedField.className = field.className || field.class || null;
            delete sanitizedField.class;

            if (field.values) {
                field.values = field.values.map(function (option) {
                    return utils.trimObj(option);
                });
            }

            return utils.trimObj(sanitizedField);
        },

        // Render multiple page form
         renderMultiPageForm = function (element, pages) {
            if (opts.pageTitles) {
                var pageTitles = JSON.parse(opts.pageTitles);
            }

            var multiPageWrap = $('<div data-amcform-js="multi-page" class="amcform-multi-page fieldset"></div>')
                    .appendTo(element),
                pageTitlesWrap = $('<ul data-amcform-js="page-titles" class="amcform-page-titles"></ul>')
                    .appendTo(multiPageWrap);

            if (typeof pageTitles != 'undefined') {
                // Generate Titles
                $.each(pageTitles, function (index, title) {
                    var step = index + 1;

                    pageTitlesWrap.append('<li class="amcform-title-wrap"><a href="#page-' + index
                        + '" class="amcform-title"><p class="amcform-step">'
                        + step + '</p><span class="amcform-label">' + title + '</span></a></li>');
                });
            }

            // Generate pages
            for (var i = 0; i < pages.length; i++) {
                var pageWrap = $('<div id="page-' + i + '" class="amcform-page-wrap fields"></div>')
                        .appendTo(multiPageWrap)[0],
                    toolbar = '<div class="amcform-toolbar">';

                pageWrap.appendFormFields(pages[i]);

                if (i > 0) {
                    toolbar += '<button type="button" '
                        + 'data-amcform-js="prev-button" class="amcform-prev action submit primary ">'
                        + $.mage.__('Previous') + '</button>';
                }

                if (i === pages.length - 1) {
                    if ($('[data-amcform-js="gdpr"]').length) {
                        var gdpr =  $(element).next('[data-amcform-js="gdpr"]').clone().appendTo(pageWrap),
                            inputId = gdpr.find('input').attr('data-id');

                        gdpr.find('input').prop('disabled', false).attr('id', inputId);
                        gdpr.show();
                    }
                    var form = $(element).closest('.amform-form'),
                        isSurvey = parseInt($(form).find('[name="is_survey"]').attr('value'), 10),
                        prompt = '';

                    if (isSurvey) {
                        prompt = 'data-mage-init=\'{"amcformPrompt": {}}\'';
                    }
                    toolbar += '<button type="submit" data-amcform-js="submit-button" '
                        + prompt + ' class="amcform-submit action submit primary ">'
                        + opts.submitButtonTitle + '</button>';
                } else {
                    toolbar += '<button type="button" data-amcform-js="next-button" '
                        + 'class="amcform-next action submit primary ">' + $.mage.__('Next') + '</button>';
                }

                if (i > 0) {
                    $(pageWrap).find('input, textarea').addClass('amcform-hidden-page');
                }

                $(toolbar + '</div>').appendTo(pageWrap);
            }

             window.dispatchEvent(new CustomEvent('amform-elements-rendered', {
                 detail: {
                     form: $(element).closest('form')
                 }
             }));
        },

        // Begin the core plugin
         page,
            pages = [],
            rendered,
            dependencyFields = [],
            dependencyMap = [];

        // generate field markup if we have fields
        if (opts.formData) {
            if (!Array.isArray(opts.formData[0])) {
                var createPage = [];

                createPage.push(opts.formData);
                opts.formData = createPage;
            }
            // Pages
            for (var i = 0; i < opts.formData.length; i++) {
                rendered = [];
                page = opts.formData[i];
                // Forms
                for (var j = 0; j < page.length; j++) {
                    var sanitizedField = santizeField(page[j]);

                    switch (sanitizedField.type) {
                        case 'textinput':
                            try {
                                var validation = sanitizedField.validation_fields;

                                if (!validation) {
                                    validation = JSON.parse(sanitizedField.validation);
                                }
                            } catch (e) {
                                validation = {};
                            }
                            var resultStr = '',
                                begStr = '{',
                                endStr = '}';

                            resultStr = begStr + resultStr;

                            if (validation.hasOwnProperty('validation')) {
                                switch (validation.validation) {
                                    case 'None':
                                    case ' ':
                                        break;
                                    case 'pattern':
                                        if (sanitizedField.regexp) {
                                            var validationName = validation.validation + Math.random(),
                                                errorMessage = sanitizedField.errorMessage || 'Invalid format.';

                                            $.validator.addMethod(
                                                validationName,
                                                function (value, element, param) {
                                                    return this.optional(element) || new RegExp(param).test(value);
                                                },
                                                $.mage.__(errorMessage)
                                            );

                                            resultStr = resultStr + '\'' +
                                                validationName + '\':' + sanitizedField.regexp;
                                        }
                                        break;
                                    default:
                                        resultStr = resultStr + '\'' + validation.validation + '\':true';
                                }
                            }

                            resultStr += endStr;
                            sanitizedField['data-validate'] = resultStr;
                            delete sanitizedField.validation;
                            break;
                    }
                    if (sanitizedField.dependency) {
                        // eslint-disable-next-line no-loop-func
                        $.each(sanitizedField.dependency, function (index, elem) {
                            var name = sanitizedField.name;

                            dependencyFields.push(elem.field);

                            if (
                                ['checkbox', 'checkboxtwo', 'listbox'].indexOf(sanitizedField.type) !== -1
                                || ['file'].indexOf(sanitizedField.type) !== -1
                                && ['1'].indexOf(sanitizedField.multiple) !== -1
                            ) {
                                name += '[]';
                            }

                            if (!dependencyMap[name]) {
                                dependencyMap[name] = [];
                            }

                            dependencyMap[name].push(elem);
                        });
                    }

                    rendered.push(utils.fieldRender(sanitizedField, opts));
                }

                pages.push(rendered);
            }

            if (opts.render) {
                if (opts.container) {
                    opts.container = opts.container instanceof jQuery ? opts.container[0] : opts.container;
                    opts.container.emptyContainer();
                    renderMultiPageForm(opts.container, pages);
                } else if (element) {
                    element.emptyContainer();
                    renderMultiPageForm(element, pages);
                }

                runCallbacks();
                opts.notify.success(opts.messages.formRendered);
            } else {
                $.each(pages, function (index, page) {
                    formRender.markup = rendered.map(function (page) {
                        return elem.innerHTML;
                    }).join('');
                });
            }
        } else {
            var noData = utils.markup('div', opts.messages.noFormData, {
                className: 'no-form-data'
            });

            pages.push(noData);
            opts.notify.error(opts.messages.noFormData);
        }
        this.dependencyMap = dependencyMap;
        this.dependencyFileds = dependencyFields;
        this.element = $(element);
        this.form = this.element.parents('form');
        this.element.on('click change', 'input, select', function ($, fbUtils, event) {
            var target = event.currentTarget;

            if (this.dependencyFileds.indexOf(target.name.replace('[]', '')) !== -1) {
                fbUtils.updateDependency(this, $);
            }
        }.bind(this, $, fbUtils));

        fbUtils.updateDependency(this, $);

        if (opts.ajax_submit === 1) {
            var form = this.form;

            form.unbind('submit');
            form.on('submit', fbUtils.submitForm.bind(form));
            form.trigger('ajaxFormLoaded');
        }

        return formRender;
    }

    (function ($) {
        $.fn.formRender = function (formId, options, isSurvey, callback) {
            var self = this,
                componentOptions = {
                    classes: {
                        active: '-active',
                        formEdit: '-form-edit',
                        grid: '-grid',
                        title: 'amcform-title'
                    },
                    selectors: {
                        formParent: '.amform-parent',
                        popupBlock: '.amcform-popup-block',
                        closeButton: '[data-amcform-js="close"]',
                        map: '.map',
                        showPopupButton: '[data-amform-show={formId}]',
                        popupCloseException: '.amcform-popup, .amform-show-popup, .modals-overlay, ' +
                            '.modal-inner-wrap, .ui-datepicker-next, .ui-datepicker-prev, .am-picker-year',
                        popupBlockId: '.amcform-popup-block[data-form-id={formId}]',
                        form: '.amform-form[data-amform-id={id}]',
                        insertContainer: '.insert-container'
                    }
                };

            if (parseInt(isSurvey, 10)) {
                $.ajax({
                    url: urlBuilder.build(window.location.origin + '/amasty_customform/form/survey'),
                    dataType: 'json',
                    type: 'post',
                    data: {'form_id': formId},
                    success: function (response) {
                        if (response.isSurveyAvailable) {
                            // eslint-disable-next-line no-use-before-define
                            generateBegin(formId, options, callback);
                        } else {
                            // eslint-disable-next-line no-use-before-define
                            renderMessage(
                                $(componentOptions.selectors.form.replace('{id}', formId)),
                                'Thank you for participating in this survey!'
                            );
                        }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            } else {
                // eslint-disable-next-line no-use-before-define
                generateBegin(formId, options, callback);
            }

            // eslint-disable-next-line no-use-before-define
            addShowPopupButtonListener();

            /**
             * @param {Object} target - jQuery node/nodes-list
             * @param {String} message
             * @returns {void}
             */
            function renderMessage(target, message) {
                if (!target.length) {
                    return;
                }

                target.each(function (index, item) {
                    $(item)
                        .find(componentOptions.selectors.insertContainer)
                        .html($('<h3>', {
                            text: $.mage.__(message),
                            class: componentOptions.classes.title
                        }));
                });
            }

            function generateBegin(formId, options, callback) {
                // eslint-disable-next-line no-use-before-define
                generateForm(formId, options);
                if (callback) {
                    callback();
                }
            }

            function generateForm(formId, options) {
                self.each(function () {
                    var formRender = new FormRenderFn(formId, options, this);

                    $(window).trigger('amform-form-' + formId, [
                        formRender.form[0],
                        formRender.form.find('[type="submit"]')[0]
                    ]);

                    return formRender;
                });
            }

            function addShowPopupButtonListener() {
                var buttonBlock = $(componentOptions.selectors.showPopupButton.replace('{formId}', formId)),
                    _formId,
                    popupWrapper,
                    popupBlock,
                    googleMaps;

                buttonBlock.on('click', function () {
                    _formId = $(this).attr('data-amform-show');
                    popupWrapper = $(this).closest(componentOptions.selectors.formParent);
                    popupBlock = popupWrapper
                        .find(componentOptions.selectors.popupBlockId
                        .replace('{formId}', _formId));
                    googleMaps = popupBlock.find(componentOptions.selectors.map);

                    if (!popupBlock.length) {
                        return;
                    }

                    popupBlock.addClass(componentOptions.classes.active);

                    if (googleMaps.width()) {
                        // trigger resize for prevent grey map
                        googleMaps.width(googleMaps.width() + 1);
                    }

                    $(document).on('click', function (event) {
                        if (!$(event.target).closest(componentOptions.selectors.popupCloseException).length) {
                            if (buttonBlock.hasClass(componentOptions.classes.formEdit)) {
                                popupWrapper.remove();
                            }

                            popupBlock.removeClass(componentOptions.classes.active);
                        }
                    });

                    popupBlock.find(componentOptions.selectors.closeButton).on('click', function () {
                        if (buttonBlock.hasClass(componentOptions.classes.formEdit)) {
                            popupWrapper.remove();
                        }

                        popupBlock.removeClass(componentOptions.classes.active);
                    });
                });

                if (buttonBlock.hasClass(componentOptions.classes.formEdit)) {
                    // eslint-disable-next-line no-use-before-define
                    formEditEvents(buttonBlock);
                }
            }

            function formEditEvents(buttonBlock) {
                var popupWrapper = buttonBlock.closest(componentOptions.selectors.formParent),
                    popupBlock = popupWrapper.find(componentOptions.selectors.popupBlock),
                    dataProvider = require('amcformGridDataProvider'),
                    messageList = require('amcformMessageList'),
                    notifications = require('amcformNotifications'),
                    gridData = dataProvider().itemStorage.get();

                buttonBlock.trigger('click');

                buttonBlock.closest(componentOptions.selectors.formParent).find('form').submit(function (event) {
                    event.preventDefault();

                    // eslint-disable-next-line no-use-before-define
                    submitEditedForm($(this), function (response) {
                        if (!response.messages.length) {
                            notifications().setMessageType('edited', 1);

                            if (buttonBlock.hasClass(componentOptions.classes.grid)) {
                                dataProvider().getForms(gridData.currentPage, gridData.pageSize);
                                notifications('showSuccessMessage');
                            } else {
                                window.location.reload();
                            }
                        } else if (messageList) {
                            response.messages.forEach(function (message) {
                                messageList.addErrorMessage({message: message});
                            });
                        }

                        popupBlock.removeClass(componentOptions.classes.active);
                        popupWrapper.remove();
                    });
                });
            }

            function submitEditedForm(target, callback) {
                var formData;

                if (target.length) {
                    formData = new FormData(target.get(0));

                    $.ajax({
                        type: 'POST',
                        url: target.attr('action'),
                        data: formData,
                        contentType: false,
                        processData:false,
                        dataType: 'json'
                    }).done(callback);
                }
            }
        };
    })($);
});
