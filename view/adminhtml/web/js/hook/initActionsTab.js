/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'underscore',
    'Mageplaza_Webhook/js/lib/codemirror',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Mageplaza_Webhook/js/mode/xml/xml',
    'Mageplaza_Webhook/js/addon/display/autorefresh',
    'Mageplaza_Webhook/js/addon/mode/overlay',
    'jquery/ui'
], function ($, _, CodeMirror, modal, $t) {
    "use strict";
    var payloadUrl = $("#hook_payload_url");
    $.widget('mageplaza.initActionsTab', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initCodeMirror();
            this.initPopup();
            this.initObservable();
            this.initDragable();
        },
        initObservable: function () {
            this.rowSelectObs();
            this.inputObs();
            this.removeModifierObs();
            this.addModifierObs();
            this.preview();
        },
        preview: function () {
            $('body').on('click', '#grid_tab_preview', function () {
                var preview = 'curl -H "Authorization: Token token=sfg999666t673t7t82"';
                var contentType = $('#hook_content_type').val();
                if (contentType) {
                    preview += ' -H "Content-Type: ' + contentType + '"';
                }
                var headers = $('#hook_headers :input').serializeArray();
                _.each(headers, function (object, key) {
                    if (key % 2 === 0) {
                        preview += " -H '" + object.value + ': ';
                    } else {
                        preview += object.value + "'";
                    }
                });

                var body = $('#hook_body').val();
                if (body && (contentType === 'application/json' || contentType === 'application/json; charset=UTF-8')) {
                    try {
                        body = JSON.stringify(eval(body));
                    } catch (e) {
                        console.log($t('Please correct your json data'))
                    }
                }

                preview += " -d '" + body + "'";
                var url = $('#hook_payload_url').val();
                var method = $('#hook_method').val();
                if (!method) {
                    method = 'GET';
                }
                preview += " -X " + method + " '" + url + "'";

                $('#hook_preview').val(preview);

            });
        },
        initPopup: function () {
            var options = {
                type: 'slide',
                responsive: true,
                innerScroll: true,
                title: $t('Insert Variable'),
                subTitle: $t('Click to each attribute to add filter & insert it to template'),
                buttons: []
            };
            this.popup = modal(options, $('#insert-variable-popup'));
            var self = this;
            $('a#insert-variable').click(function () {
                self.popup.openModal();
            });
            $('a#insert-variable-upload').click(function () {
                self.popup.element.addClass('upload');
                self.popup.openModal();
            });
        },
        initDragable: function () {
            var self = this;
            $('#insert-variable-popup .modifier-group').sortable({
                stop: function (event, ui) {
                    var attr_code = $(this).attr('code');
                    self.updateVariable(attr_code);
                }
            });
        },
        initCodeMirror: function () {
            var self = this;
            this.options.codeMirror = CodeMirror.fromTextArea(document.getElementById("hook_body"), {
                mode: 'xml',
                lineNumbers: true,
                autofocus: true,
                autoRefresh: true,
                styleActiveLine: true,
                viewportMargin: Infinity
            });
            this.options.codeMirror.addOverlay({
                token: function (stream) {
                    var query = /^{{.*?}}/g;
                    if (stream.match(query)) {
                        return 'liquid-variable';
                    }
                    stream.next();
                }
            });
            this.options.codeMirror.addOverlay({
                token: function (stream) {
                    var query = /^{%.*?%}/g;
                    if (stream.match(query)) {
                        return 'liquid-method';
                    }
                    stream.next();
                }
            });
            this.options.codeMirror.on('change', function (cMirror) {
                $("#hook_body").val(cMirror.getValue());
            });
            this.options.doc = this.options.codeMirror.getDoc();
            $('.insert').on('click', function () {
                var addText = $(this).siblings('.liquid-variable').text();
                var cursor;
                if (self.popup.element.hasClass('upload')) {
                    cursor = payloadUrl[0].selectionStart;
                    var textAreaTxt = payloadUrl.val();
                    payloadUrl.val(textAreaTxt.substring(0, cursor) + addText + textAreaTxt.substring(cursor));
                } else {
                    cursor = self.options.doc.getCursor();
                    self.options.doc.replaceRange(addText, cursor);
                }
                self.popup.element.removeClass('upload');
                $('[data-role="closeBtn"].action-close').trigger('click');
            });
        },
        rowSelectObs: function () {
            var self = this;
            $('#insert-variable-popup').on('change', 'select', function () {
                var elf = $(this);
                var paramsEl = elf.siblings('.params');
                var attr_code = elf.parents('.modifier').attr('code');
                paramsEl.html('');
                if (elf.val() !== 0) {
                    _.each(self.options.modifiersData[this.value].params, function (record, index) {
                        paramsEl.append('<span class="modifier-param">' + record.label + '</span><input class="modifier-param" type="text" code="' + attr_code + '"/>')
                    });
                }
                self.updateVariable(attr_code);
            });
        },
        inputObs: function () {
            var self = this;
            $('#insert-variable-popup').on('change', 'input', function () {
                var attr_code = $(this).attr('code');
                self.updateVariable(attr_code);
            });
        },
        removeModifierObs: function () {
            var self = this;
            $('#insert-variable-popup').on('click', '.remove-modifier', function () {
                var rowModifier = $(this).parent().parent().parent();
                var attr_code = $(this).parents('.modifier').attr('code');
                $(this).parent().parent().remove();
                self.updateVariable(attr_code);

                if (!rowModifier.children().length) {
                    rowModifier.parent().removeClass('show');
                }
            });
        },
        addModifierObs: function () {
            var self = this;
            $('#insert-variable-popup').on('click', '.add-modifier', function () {
                var rowModifier = $(this).parent().siblings('.row-modifier');
                if (!rowModifier.hasClass('show')) {
                    rowModifier.addClass('show');
                }

                var opt = '';
                var attr_code = $(this).parents('.attr-code').attr('code');
                _.each(self.options.modifiersData, function (record, index) {
                    opt += '<option value="' + index + '">' + record.label + '</option>';
                });
                var modifierEl = '<div class="modifier" code="' + attr_code + '"><div class="row"><select><option value="0">' + $t('--Please Select--') + '</option>' +
                    opt +
                    '</select><div class="params"></div><button title="Delete" type="button" class="action- scalable delete delete-option remove-modifier"><span>Delete</span></button></div></div>';
                $(this).parent().parent().find('.modifier-group').append(modifierEl);
            });
        },
        updateVariable: function (attr_code) {
            var parentEl = $('[code="' + attr_code + '"]');
            var str = '{{ ';
            str += 'item.' + attr_code;
            parentEl.find('.modifier').each(function () {
                var modifier = $(this).find('select').val();
                if (modifier && modifier !== '0') {
                    str += ' | ' + modifier;
                }
                var params = $(this).find('input.modifier-param');
                if (params.length) {
                    str += ': ';

                    params.each(function (index) {
                        if (index === (params.length - 1)) {
                            str += "'" + this.value + "'";
                            return;
                        }
                        str += "'" + this.value + "', ";
                    });
                }
            });
            str += ' }}';
            parentEl.find('.liquid-variable').text(str);
        }
    });
    return $.mageplaza.initActionsTab;
});
