<?php

namespace kartik\markdown;

use yii\web\AssetBundle;

/**
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 */
class MarkdownEditorAsset extends AssetBundle {

    public $sourcePath = '@vendor/kartik-v/yii2-markdown/kartik/assets';
    public $css = [
        'css/kv-markdown.css',
    ];
    public $js = [
		'js/rangyinputs-jquery-1.1.2.js',
        'js/kv-markdown.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

}
