<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-markdown
 * @version 1.1.0
 */

namespace kartik\markdown;

/**
 * Asset bundle for MarkdownEditor Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class MarkdownEditorAsset extends \kartik\base\AssetBundle
{

    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/kv-markdown']);
        $this->setupAssets('js', ['js/rangyinputs-jquery-1.1.2', 'js/kv-markdown']);
        parent::init();
    }

}