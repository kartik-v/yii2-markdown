<?php

/**
 * @package   yii2-markdown
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.3.1
 */

namespace kartik\markdown\controllers;

use Yii;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use kartik\base\Config;
use kartik\markdown\Module;
use kartik\markdown\Markdown;

class ParseController extends \yii\web\Controller
{
    /**
     * Convert markdown text to HTML for preview
     *
     * @returns JSON encoded HTML output
     */
    public function actionPreview()
    {
        $output = '';
        $module = Config::getModule(Module::MODULE);
        if (isset($_POST['source'])) {
            $output = (strlen($_POST['source']) > 0) ? Markdown::convert($_POST['source'], ['custom' => $module->customConversion]) : $_POST['nullMsg'];
        }
        echo Json::encode(HtmlPurifier::process($output));
    }

    /**
     * Download the exported file
     */
    public function actionDownload()
    {
        if (empty($_POST) || empty($_POST['export_filetype'])) {
            return $this->renderPartial('download');
        }
        $type = empty($_POST['export_filetype']) ? 'htm' : $_POST['export_filetype'];
        $name = empty($_POST['export_filename']) ? Yii::t('kvgrid', 'export') : $_POST['export_filename'];
        $content = empty($_POST['export_content']) ? Yii::t('kvgrid', 'No data found') : $_POST['export_content'];
        $this->setHttpHeaders($type, $name);
        return $content;
    }

    /**
     * Sets the HTTP headers needed by file download action.
     */
    protected function setHttpHeaders($type, $name)
    {
        $mime = ($type == 'htm') ? 'text/html' : 'text/plain';
        Yii::$app->getResponse()->getHeaders()
                 ->set('Pragma', 'public')
                 ->set('Expires', '0')
                 ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                 ->set('Content-Disposition', 'attachment; filename="' . $name . '.' . $type . '"')
                 ->set('Content-type', $mime . '; charset=utf-8');
    }
}