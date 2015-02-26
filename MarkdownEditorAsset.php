<?php

/**
 * @package   yii2-markdown
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.3.1
 */

namespace kartik\markdown;

/**
 * Asset bundle for MarkdownEditor Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class MarkdownEditorAsset extends \kartik\base\PluginAssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/kv-markdown']);
        $this->setupAssets('js', ['js/rangyinputs-jquery-1.1.2', 'js/kv-markdown']);
        parent::init();
    }
}