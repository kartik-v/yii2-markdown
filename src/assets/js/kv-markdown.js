/*!
 * ====================================================
 * kv-markdown.js
 * ====================================================
 * A markdown editor jquery plugin parsed using PHP Markdown Extra  
 * and PHP SmartyPants. Designed for Yii Framework 2.0
 *
 * https://github.com/kartik-v/yii2-markdown
 * 
 * Copyright (c) 2015 - 2018, Kartik Visweswaran  
 * Krajee.com  
 * Licensed under BSD-3 License. 
 * Refer attached LICENSE.md for details. 
 * Version: 1.3.1
 */
 
(function (factory) {
    "use strict";
    if (typeof define === 'function' && define.amd) { // jshint ignore:line
        // AMD. Register as an anonymous module.
        define(['jquery'], factory); // jshint ignore:line
    } else { // noinspection JSUnresolvedVariable
        if (typeof module === 'object' && module.exports) { // jshint ignore:line
            // Node/CommonJS
            // noinspection JSUnresolvedVariable
            module.exports = factory(require('jquery')); // jshint ignore:line
        } else {
            // Browser globals
            factory(window.jQuery);
        }
    }
}(function ($) {
    "use strict";
    var $h, KvMarkdown;
    String.prototype.trimRight = function (charlist) {
        if (charlist === undefined) {
            charlist = "\s";
        }
        return this.replace(new RegExp("[" + charlist + "]+$"), "");
    };

    String.prototype.repeat = function (n) {
        n = n || 1;
        return Array(n + 1).join(this);
    };
    
    $h = {
        el: function (id) {
            return $('#' + id);
        },
        isEmpty: function (value, trim) {
            return value === null || value === undefined || value == []
                || value === '' || trim && $.trim(value) === '';
        },
        isNumber: function (n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        },
        getMarkUp: function (txt, begin, end) {
            var m = begin.length, n = end.length, str = txt;
            if (m > 0) {
                str = (str.slice(0, m) == begin) ? str.slice(m) : begin + str;
            }
            if (n > 0) {
                str = (str.slice(-n) == end) ? str.slice(0, -n) : str + end;
            }
            return str;
        },
        getBlockMarkUp: function(txt, begin, end) {
            var list = [];
            if (txt.indexOf('\n') < 0) {
                return $h.getMarkUp(txt, begin, end);
            } 
            list = txt.split('\n');
            $.each(list, function (k, v) {
                list[k] = $h.getMarkUp(v.trimRight(), begin, end + '  ')
            });
            return list.join('\n');
        },
        markup: function (btn, txt) {
            var link, list = [], str = txt, len = txt.length, ind = '  ', i = 1;
            switch (btn) {
                case 1: // Bold
                    return (len > 0) ? $h.getBlockMarkUp(txt, '**', '**') : '**(bold text here)**';
                case 2: // Italic
                    return (len > 0) ? $h.getBlockMarkUp(txt, '*', '*') : '*(italic text here)*';
                case 3: // Paragraph
                    return (len > 0) ? $h.getMarkUp(txt, '\n', '\n') : '\n(paragraph text here)\n';
                case 4:  // New Line
                    return $h.getBlockMarkUp(txt, '', '  ');
                case 5:  // Hyperlink
                    link = prompt('Insert Hyperlink', 'http://')
                    return (link != null && link != '' && link != 'http://') ? '[' + txt + '](' + link + ')' : txt;
                case 6:  // Image
                    link = prompt('Insert Image Hyperlink', 'http://')
                    return (link != null && link != '' && link != 'http://') ? '![' + txt + '](' + link + ' "enter image title here")' : txt;
                case 7:  // Add Indent
                    if (str.indexOf('\n') < 0) {
                        str = ind + str
                    } else {
                        list = txt.split('\n');
                        $.each(list, function (k, v) {
                            list[k] = ind + v;
                        })
                        str = list.join('\n');
                    }
                    return str;
                case 8:  // Remove Indent
                    if (str.indexOf('\n') < 0 && str.substr(0, 2) == ind) {
                        str = str.slice(2);
                    } else {
                        list = txt.split('\n');
                        $.each(list, function (k, v) {
                            list[k] = v;
                            if (v.substr(0, 2) == ind) {
                                list[k] = v.slice(2);
                            }
                        })
                        str = list.join('\n');
                    }
                    return str;
                case 9:  // Unordered List
                    return $h.getBlockMarkUp(txt, "- ", "");
                case 10:  // Ordered List
                    var start = prompt('Enter starting number', 1);
                    if (start != null && start != '') {
                        if (!$h.isNumber(start)) {
                            start = 1;
                        }
                        if (txt.indexOf('\n') >= 0) {
                            i = start;
                            list = txt.split('\n');
                            $.each(list, function (k, v) {
                                list[k] = $h.getMarkUp(v, i + '. ', '');
                                i++;
                            })
                            return list.join('\n');
                        }
                        return $h.getMarkUp(txt, start + '. ', '');
                    }
                    return str;
                case 11:  //  Definition List
                    if (txt.indexOf('\n') > 0) {
                        list = txt.split('\n')
                        $.each(list, function (k, v) {
                            tag = (i % 2 == 0) ? ':    ' : '';
                            list[k] = $h.getMarkUp(v, tag, '');
                            i++;
                        })
                        return list.join('\n');
                    }
                    return txt + "\n:    \n";
                case 12: // Footnote
                    var title = 'Enter footnote ', notes = '';
                    if (txt.indexOf('\n') < 0) {
                        notes = '[^1]: ' + title + '1\n';
                        return $h.getMarkUp(txt, '', title + '[^1]') + "\n" + notes;
                    } 
                    list = txt.split('\n');
                    $.each(list, function (k, v) {
                        id = '[^' + i + ']';
                        list[k] = $h.getMarkUp(v, '', id + '  ');
                        notes = notes + id + ': ' + title + i + '\n';
                        i++;
                    })
                    return list.join('\n') + "  \n\n" + notes;
                case 13: // Blockquote
                    return $h.getBlockMarkUp(txt, "> ", "  ");
                case 14: // Inline Code
                    return $h.getMarkUp(txt, "`", "`");
                case 15: // Code Block
                    var lang = prompt('Enter code language (e.g. html)', '');
                    if (isEmpty(lang, true)) {
                        lang = '';
                    }
                    return $h.getMarkUp(txt, "~~~" + lang + " \n", "\n~~~  \n");
                case 16:  // Horizontal Line
                    return $h.getMarkUp(txt, '', '\n- - -');
                default:
                    if (btn > 100) { // Header
                        var n = btn - 100, pad = "#".repeat(n);
                        return $h.getMarkUp(txt, pad + " ", " " + pad);
                    }
            }
            return txt;
        }
    };
    KvMarkdown = function (element, options) {
        var self = this;
        self.$element = $(element);
        self.options = options;
        self.init();
    };
    KvMarkdown.prototype = {
        constructor: KvMarkdown,
        init: function() {
            var self = this, options = self.options;
            self.$container = $h.el(options.containerId);
            self.$editor = $h.el(options.editorId);
            self.$previewContainer = $h.el(options.previewContainerId);
            self.$previewButton = $h.el(options.previewButtonId);
            self.$maximizeButton = $h.el(options.maximizeButtonId);
            self.$export1Button = $h.el(options.export1ButtonId);
            self.$export2Button = $h.el(options.export2ButtonId);

            self.$element.on('focus', function () {
                self.$editor.addClass('active');
            });

            self.$element.on('blur', function () {
                self.$editor.removeClass('active');
            });
            
            self.$container.find('[data-tool="md-button"]').on('click', function(e) {
                e.preventDefault();
                self.markup($(this).data('key'));
            });

            self.$previewButton.on('click', function() {
                self.togglePreview();
            });

            self.$export1Button.on('click', function(e) {
                e.preventDefault();
                self.genExportFile('Text');
            });

            self.$export2Button.on('click', function(e) {
                e.preventDefault();
                self.genExportFile('HTML');
            });

            self.$maximizeButton.on('click', function(){
                $(this).toggleClass('active');
                self.setHeight(true);
            });
            
            self.setHeight();
        },
        setHeight: function(togFull) {
            var self = this, c = self.$container.outerHeight(), h = self.options.height;
            if (togFull) {
                self.$container.toggleClass('kv-fullscreen');
            }
            if (self.$container.hasClass('kv-fullscreen')) {
                h = $(window).height() - c + h;
            }
            self.$element.css('height', h);
            self.$previewContainer.css('height', h);
        },
        enableButtons: function () {
            var self = this;
            self.$editor.find('button').each(function () {
                $(this).removeAttr('disabled')
            });
        },
        disableButtons: function() {
            var self = this;
            self.$editor.find('button').each(function () {
                if ($(this).attr('data-enabled') == undefined) {
                    $(this).attr('disabled', 'disabled')
                }
            });
        },
        markup: function (btn) {
            var self = this, $el = self.$element;
            $el.focus();
            var txt = $el.extractSelectedText(), str = $h.markup(btn, txt);
            if (!$h.isEmpty(str)) {
                $el.replaceSelectedText(str, "select");
            }
        },
        genExportFile: function (vType) {
            var self = this, opts = self.options, output = opts.exportHeader + self.$element.val();
            if (vType == 'HTML') {
                $.ajax({
                    type: "POST",
                    url: opts.previewAction,
                    dataType: "json",
                    data: {
                        source: output,
                        nullMsg: opts.nullMsg
                    },
                    success: function (data) {
                        if (data) {
                            var html = opts.exportMeta + opts.exportCss + data;
                            self.download('htm', html);
                        } else {
                            alert('HTML conversion failed! Try again later.'); // debug purposes
                        }
                    }
                });
            } else {
                self.download('txt', output);
            }
        },
        togglePreview: function() {
            var self = this;
            self.$previewButton.toggleClass('active');
            if (self.$previewContainer.hasClass('hidden')) {
                self.disableButtons()
                self.$previewButton.removeAttr('disabled')
                self.$element.addClass('hidden');
                self.$previewContainer.removeClass('hidden');
                $.ajax({
                    type: "POST",
                    url: self.options.previewAction,
                    dataType: "json",
                    data: {
                        source: self.$element.val(),
                        smarty: self.options.smarty,
                        nullMsg: self.options.nullMsg
                    },
                    beforeSend: function () {
                        self.$previewContainer.html(self.options.progress);
                    },
                    success: function (data) {
                        self.$previewContainer.html(data);
                    }
                });
                self.$previewContainer.focus();
            } else {
                self.enableButtons();
                self.$element.removeClass('hidden');
                self.$previewContainer.addClass('hidden');
                self.$element.focus();
            }
        },
        download: function (type, content) {
            var self = this, ifrm = self.$element.attr('id') + '-iframe', id = '#' + ifrm,
                $type = $('<input/>', {'name': 'export_filetype', 'value': type, 'type': 'hidden'}),
                $file = $('<input/>', {'name': 'export_filename', 'value': self.options.filename, 'type': 'hidden'}),
                $csrf = $('<input/>', {'name': yii.getCsrfParam() || '_csrf', 'value': yii.getCsrfToken(), 'type': 'hidden'}),
                $content = $('<textarea/>', {'name': 'export_content'}).val(content), url = self.options.downloadAction,
                formAttribs = {'action': url, 'target': ifrm, 'method': 'post', css: {'display': 'none'}};
            if (!$(id).length) {
                $('<iframe/>', {name: ifrm, css: {'display': 'none'}}).appendTo('body');
            }
            $('<form/>', formAttribs).append($type, $file, $csrf, $content).appendTo('body').submit().remove();
        }
    };
    $.fn.kvMarkdown = function (option) {
        var args = Array.apply(null, arguments), retvals = [];
        args.shift();
        this.each(function () {
            var self = $(this), data = self.data('kvMarkdown'), options = typeof option === 'object' && option, opt;
            if (!data) {
                opt = $.extend(true, {}, options, self.data());
                data = new KvMarkdown(this, opt);
                self.data('kvMarkdown', data);
            }

            if (typeof option === 'string') {
                retvals.push(data[option].apply(data, args));
            }
        });
        switch (retvals.length) {
            case 0:
                return this;
            case 1:
                return retvals[0];
            default:
                return retvals;
        }
    };
})); 
