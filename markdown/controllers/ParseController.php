<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2013
 * @package yii2-markdown
 * @version 1.0.0
 */

namespace kartik\markdown\controllers;

use Yii;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use kartik\markdown\Markdown;

class ParseController extends \yii\web\Controller {

    /**
     * Convert markdown text to HTML for preview
     * @returns JSON encoded HTML output
     */
    public function actionPreview() {
        $output = '';
        $module = Yii::$app->controller->module;
        if (isset($_POST['source'])) {
            $output = (strlen($_POST['source']) > 0) ? HtmlPurifier::process(Markdown::convert($_POST['source'], ['custom' => $module->customConversion])) : $_POST['nullMsg'];
        }
        echo Json::encode($output);
    }

}
