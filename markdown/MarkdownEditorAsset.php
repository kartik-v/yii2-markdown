<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2013
 * @package yii2-markdown
 * @version 1.0.0
 */

namespace kartik\markdown;

use yii\web\AssetBundle;

/**
 * Asset bundle for MarkdownEditor Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class MarkdownEditorAsset extends AssetBundle {

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init() {
        $this->sourcePath = __DIR__ . '/../assets';
        $this->css = YII_DEBUG ? ['css/kv-markdown.css'] : ['css/kv-markdown.min.css'];
        $this->js = YII_DEBUG ? ['js/rangyinputs-jquery-1.1.2.js', 'js/kv-markdown.js'] : ['js/rangyinputs-jquery-1.1.2.min.js', 'js/kv-markdown.min.js'];
        parent::init();
    }

}
