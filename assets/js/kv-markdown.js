/*!
 * ====================================================
 * kv-markdown.js
 * ====================================================
 * A markdown editor parser for PHP Markdown Extra and 
 * PHP SmartyPants. Designed for Yii Framework 2.0
 *
 * https://github.com/kartik-v/yii2-markdown
 * 
 * Copyright (c) 2015, Kartik Visweswaran  
 * Krajee.com  
 * Licensed under BSD-3 License. 
 * Refer attached LICENSE.md for details. 
 * Version: 1.3.1
 */
String.prototype.trimRight = function (charlist) {
    if (charlist === undefined) {
        charlist = "\s";
    }
    return this.replace(new RegExp("[" + charlist + "]+$"), "");
};

String.prototype.repeat = function (n) {
    n = n || 1;
    return Array(n + 1).join(this);
}

function isEmpty(value, trim) {
    return value === null || value === undefined || value == []
        || value === '' || trim && $.trim(value) === '';
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function download(form, filename, type, content) {
    form.find('[name="export_filetype"]').val(type);
    form.find('[name="export_filename"]').val(filename);
    form.find('[name="export_content"]').val(content);
    form.submit();
};

function genExportFile(form, filename, vCss, vMeta, vHeader, vData, vType, vAlert, vUrl, vNullMsg) {
    alert(vAlert)
    output = vHeader + vData
    if (vType == 'HTML') {
        $.ajax({
            type: "POST",
            url: vUrl,
            dataType: "json",
            data: {
                source: output,
                nullMsg: vNullMsg
            },
            success: function (data) {
                if (data) {
                    download(form, filename, 'htm', vMeta + vCss + data);
                } else {
                    alert('HTML conversion failed! Try again later.'); // debug purposes
                }
            }
        });
    } else {
        download(form, filename, 'txt', output);
    }

}

function togglePreview(params) {
    var editor = params.editor,
        preview = params.preview,
        source = params.source,
        target = params.target,
        url = params.url,
        progress = params.progress,
        nullMsg = params.nullMsg;

    $(preview).toggleClass('active');
    if ($(target).hasClass('hidden')) {
        disableButtons(editor)
        $(preview).removeAttr('disabled')
        $(source).addClass('hidden');
        $(target).removeClass('hidden');
        $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: {
                source: $(source).val(),
                nullMsg: nullMsg
            },
            beforeSend: function () {
                $(target).html(progress);
            },
            success: function (data) {
                $(target).html(data);
            }
        });
        $(target).focus();
    } else {
        enableButtons(editor)
        $(source).removeClass('hidden');
        $(target).addClass('hidden');
        $(source).focus();
    }
}

function enableButtons(editor) {
    $(editor + ' button').each(function () {
        $(this).removeAttr('disabled')
    })
}

function disableButtons(editor) {
    $(editor + ' button').each(function () {
        if ($(this).attr('data-enabled') == undefined) {
            $(this).attr('disabled', 'disabled')
        }
    })
}

function getMarkUp(txt, begin, end) {
    var m = begin.length,
        n = end.length
    var str = txt
    if (m > 0) {
        str = (str.slice(0, m) == begin) ? str.slice(m) : begin + str
    }
    if (n > 0) {
        str = (str.slice(-n) == end) ? str.slice(0, -n) : str + end
    }
    return str;
}

function getBlockMarkUp(txt, begin, end) {
    var str = txt
    if (str.indexOf('\n') < 0) {
        str = getMarkUp(txt, begin, end);
    } else {
        var list = []
        list = txt.split('\n')
        $.each(list, function (k, v) {
            list[k] = getMarkUp(v.trimRight(), begin, end + '  ')
        })
        str = list.join('\n')
    }
    return str
}

function markUp(btn, source) {
    var el = $(source)
    el.focus();
    var txt = el.extractSelectedText(),
        len = txt.length,
        str = txt

    // Bold
    if (btn == 1) {
        str = (txt.length > 0) ? getBlockMarkUp(txt, '**', '**') : '**(bold text here)**';
    }
    // Italic
    else if (btn == 2) {
        str = (txt.length > 0) ? getBlockMarkUp(txt, '*', '*') : '*(italic text here)*';
    }
    // Paragraph
    else if (btn == 3) {
        str = (txt.length > 0) ? getMarkUp(txt, '\n', '\n') : '\n(paragraph text here)\n';
    }
    // New Line
    else if (btn == 4) {
        str = getBlockMarkUp(txt, '', '  ');
    }
    // Header
    else if (btn > 100) {
        n = btn - 100
        var pad = "#".repeat(n)
        str = getMarkUp(txt, pad + " ", " " + pad);
    }
    // Hyperlink
    else if (btn == 5) {
        link = prompt('Insert Hyperlink', 'http://')
        str = (link != null && link != '' && link != 'http://') ? '[' + txt + '](' + link + ')' : txt
    }
    // Image
    else if (btn == 6) {
        link = prompt('Insert Image Hyperlink', 'http://')
        str = (link != null && link != '' && link != 'http://') ? '![' + txt + '](' + link + ' "enter image title here")' : txt
    }
    // Add Indent
    else if (btn == 7) {
        var str = txt,
            ind = '  '
        if (str.indexOf('\n') < 0) {
            str = ind + str
        } else {
            var list = []
            list = txt.split('\n')
            $.each(list, function (k, v) {
                list[k] = ind + v
            })
            str = list.join('\n')
        }
    }
    // Remove Indent
    else if (btn == 8) {
        var str = txt,
            ind = '  '
        if (str.indexOf('\n') < 0 && str.substr(0, 2) == ind) {
            str = str.slice(2)
        } else {
            var list = []
            list = txt.split('\n')
            $.each(list, function (k, v) {
                list[k] = v
                if (v.substr(0, 2) == ind) {
                    list[k] = v.slice(2)
                }
            })
            str = list.join('\n')
        }
    }
    // Unordered List
    else if (btn == 9) {
        str = getBlockMarkUp(txt, "- ", "");
    }
    // Ordered List
    else if (btn == 10) {
        start = prompt('Enter starting number', 1)
        if (start != null && start != '') {
            if (!isNumber(start)) {
                start = 1
            }
            if (txt.indexOf('\n') < 0) {
                str = getMarkUp(txt, start + '. ', '');
            } else {
                var list = [],
                    i = start
                list = txt.split('\n')
                $.each(list, function (k, v) {
                    list[k] = getMarkUp(v, i + '. ', '')
                    i++
                })
                str = list.join('\n')
            }
        }
    }
    // Definition List
    else if (btn == 11) {
        if (txt.indexOf('\n') > 0) {
            var list = [],
                i = 1
            list = txt.split('\n')
            $.each(list, function (k, v) {
                tag = (i % 2 == 0) ? ':    ' : '';
                list[k] = getMarkUp(v, tag, '')
                i++
            })
            str = list.join('\n')
        } else {
            str = txt + "\n:    \n"
        }
    }
    // Footnote
    else if (btn == 12) {
        title = 'Enter footnote '
        notes = ''
        if (txt.indexOf('\n') < 0) {
            notes = '[^1]: ' + title + '1\n'
            str = getMarkUp(txt, '', title + '[^1]') + "\n" + notes;
        } else {
            var list = [],
                i = 1
            list = txt.split('\n')
            $.each(list, function (k, v) {
                id = '[^' + i + ']'
                list[k] = getMarkUp(v, '', id + '  ')
                notes = notes + id + ': ' + title + i + '\n'
                i++
            })
            str = list.join('\n') + "  \n\n" + notes
        }
    }
    // Blockquote
    else if (btn == 13) {
        str = getBlockMarkUp(txt, "> ", "  ");
    }
    // Inline Code
    else if (btn == 14) {
        str = getMarkUp(txt, "`", "`");
    }
    // Code Block
    else if (btn == 15) {
        lang = prompt('Enter code language (e.g. html)', '')
        if (isEmpty(lang, true)) {
            lang = '';
        }
        str = getMarkUp(txt, "~~~" + lang + " \n", "\n~~~  \n");
    }
    // Horizontal Line
    else if (btn == 16) {
        str = getMarkUp(txt, '', '\n- - -');
    }
    if (!isEmpty(str)) {
        el.replaceSelectedText(str, "select")
    }
}

function toggleScreen(btn, container, editor, modal, preview, defHeight) {
    h = $(window).height();
    if ($(btn).hasClass('active')) {
        $(btn).removeClass('active')
        val = $(modal + ' textarea').val()
        $(modal + ' textarea').height(defHeight);
        $(modal + ' ' + preview).css('max-height', defHeight);
        $(modal + ' ' + editor).clone(true).appendTo(container);
        $(container + ' textarea').val(val)
        $(modal + ' ' + editor).remove();
        $('.kv-fullscreen').modal("hide");
    } else {
        $(btn).addClass('active')
        val = $(container + ' textarea').val()
        $(container + ' ' + editor).clone(true).appendTo(modal)
        $(container + ' ' + editor).remove();
        $(modal + ' textarea').height(0.75 * h);
        $(modal + ' ' + preview).css('max-height', 0.75 * h);
        $(modal + ' textarea').val(val)
        $('.kv-fullscreen').modal("show")
    }
}

function initEditor(params) {
    var input = params.source,
        editor = params.editor,
        preview = params.preview,
        target = params.target,
        maximize = params.maximize,
        container = params.container,
        modal = editor.slice(1) + '-modal',
        export1 = params.export1,
        export2 = params.export2,
        defHeight = params.height,
        $iframe = $('#' + params.iframeId),
        $form = $iframe.contents().find('form');
    filename = params.filename;

    $(input).focus(function () {
        $(editor).addClass('active');
    });

    $(input).blur(function () {
        $(editor).removeClass('active');
    });

    $(preview).click(function () {
        togglePreview(params);
    });

    $(export1).click(function () {
        genExportFile($form, filename, '', '', params.exportHeader, $(input).val(), 'Text', params.exportText, params.url, params.nullMsg);
    });

    $(export2).click(function () {
        genExportFile($form, filename, params.exportCss, params.exportMeta, params.exportHeader, $(input).val(), 'HTML', params.exportHtml, params.url, params.nullMsg);
    });

    $('body').remove('.kv-fullscreen');
    $('body').append('<div class="modal fade kv-fullscreen" data-backdrop="static" data-keyboard=false><div class="modal-dialog"><div  id="' + modal + '" class="modal-content"></div></div></div>')
    modal = '#' + modal

    $(editor + ' ' + target).css('max-height', defHeight)
    $(editor + ' textarea').height(defHeight)

    $(maximize).click(function () {
        toggleScreen(maximize, container, editor, modal, target, defHeight);
    });
}