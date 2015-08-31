yii2-markdown
=============

[![Latest Stable Version](https://poser.pugx.org/kartik-v/yii2-markdown/v/stable)](https://packagist.org/packages/kartik-v/yii2-markdown)
[![License](https://poser.pugx.org/kartik-v/yii2-markdown/license)](https://packagist.org/packages/kartik-v/yii2-markdown)
[![Total Downloads](https://poser.pugx.org/kartik-v/yii2-markdown/downloads)](https://packagist.org/packages/kartik-v/yii2-markdown)
[![Monthly Downloads](https://poser.pugx.org/kartik-v/yii2-markdown/d/monthly)](https://packagist.org/packages/kartik-v/yii2-markdown)
[![Daily Downloads](https://poser.pugx.org/kartik-v/yii2-markdown/d/daily)](https://packagist.org/packages/kartik-v/yii2-markdown)

This module provides Markdown Editing and Conversion utilities for Yii Framework 2.0. It implements markdown conversion using PHP Markdown Extra and PHP Smarty Pants. In addition, you can customize the flavor of Markdown, by including additional custom conversion patterns. The module also includes an enhanced customized Markdown Editor Widget for markdown editing and preview at runtime. This widget is styled using Bootstrap 3.0. View a [complete demo](http://demos.krajee.com/markdown-demo).

### Markdown
[```VIEW DEMO```](http://demos.krajee.com/markdown-details/markdown-converter)  
This is a markdown converter class that uses [PHP Markdown Extra](http://michelf.ca/projects/php-markdown/extra/) and [PHP SmartyPantsTypographer](http://michelf.ca/projects/php-smartypants/typographer/) for processing Markdown conversion to HTML. It also supports configurable custom conversion processing of patterns for styling your own flavour of Markdown to some extent.
View [examples and details](http://demos.krajee.com/markdown-details/markdown-converter) or view a [complete demo](http://demos.krajee.com/markdown-demo).

### MarkdownEditor
[```VIEW DEMO```](http://demos.krajee.com/markdown-details/markdown-editor)  
This is an advanced markdown input widget with configurable options. It is styled using Bootstrap 3.0. Key features available with this widget are:

1. Configurable toolbar and buttons for formatting content
2. Live preview of Markdown formatted text as HTML
3. Maximize editor for full screen editing
4. Implements PHP Markdown Extra and PHP SmartyPantsTypographer functionality as provided by the Markdown.
5. Uses Bootstrap 3.0 styling wherever possible
6. Allows saving/exporting of the text-editor contents as Text or HTML
7. Configurable header, footer, and input options.
8. Supports localization and customization of messages and content.

View [examples and details](http://demos.krajee.com/markdown-details/markdown-editor) or view a [complete demo](http://demos.krajee.com/markdown-demo).

### Demo
You can see a [demonstration here](http://demos.krajee.com/markdown) on usage of these functions with documentation and examples.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

> Note: Check the [composer.json](https://github.com/kartik-v/yii2-markdown/blob/master/composer.json) for this extension's requirements and dependencies. 
Read this [web tip /wiki](http://webtips.krajee.com/setting-composer-minimum-stability-application/) on setting the `minimum-stability` settings for your application's composer.json.

Either run

```
$ php composer.phar require kartik-v/yii2-markdown "dev-master"
```

or add

```
"kartik-v/yii2-markdown": "dev-master"
```

to the ```require``` section of your `composer.json` file.

## Usage

### Setup Module
Add `markdown` to your modules section of your Yii configuration file
```php
'modules' = [
	/* other modules */
	'markdown' => [
		'class' => 'kartik\markdown\Module',
	]
];
```
You can setup additional configuration options for the `markdown` module:
```php
'modules' = [
	'markdown' => [
		// the module class
		'class' => 'kartik\markdown\Module',
		
		// the controller action route used for markdown editor preview
		'previewAction' => '/markdown/parse/preview',
		
		// the list of custom conversion patterns for post processing
		'customConversion' => [
			'<table>' => '<table class="table table-bordered table-striped">'
		],
		
		// whether to use PHP SmartyPantsTypographer to process Markdown output
		'smartyPants' => true
	]
	/* other modules */
];
```

### Markdown
```php
use kartik\markdown\Markdown;

// default call
echo Markdown::convert($content);

// with custom post processing
echo Markdown::convert($content, ['custom' => [
	'<h1>' => '<h1 class="custom-h1>',
	'<h2>' => '<h1 class="custom-h2>',
]]);
```

### MarkdownEditor
```php
// add this in your view
use kartik\markdown\MarkdownEditor;

// usage with model
echo MarkdownEditor::widget([
	'model' => $model, 
	'attribute' => 'markdown',
]);

// usage without model
echo MarkdownEditor::widget([
	'name' => 'markdown', 
	'value' => $value,
]);
```


### Smarty Templates
Smarty templates can be enabled globally by setting the module params
```php
'modules' = [
	'markdown' => [
	     'class' => 'kartik\markdown\Module',
	     'smarty' => true,
	     // Smarty class configuration
	     'smartyParams' => [],
	     // provide Yii::$app to the Smarty template as variable
	     'smartyYiiApp' => true,
	     // provide Yii::$app->params to the Smarty template as config variables
	     'smartyYiiParams' => true,
	],
        /* other modules */
];
```
Then define smarty in the editor
```php
echo MarkdownEditor::widget([
    'model' => $model, 
    'attribute' => 'markdown',
    'smarty' => true,
]);
```
Note that it may be unwise to enable Smarty templates globally. You can set the module property smarty to a callable function and provide RBAC features.
```php
'modules' = [
	'markdown' => [
		'class' => 'kartik\markdown\Module',
		'smarty' => function($module) {
			if (\Yii::$app->user->can('smarty')) {
			    if(\Yii::$app->user->can('smartyYiiApp'))
			        $module->smartyYiiApp=true;
			    else
			        $module->smartyYiiApp=false;
			    if(\Yii::$app->user->can('smartyYiiParams'))
			        $module->smartyYiiParams=true;
			    else
			        $module->smartyYiiParams=false;
			    return true;
			}
			return false;
		}
	],
        /* other modules */
];
```
It may be a better option to leave smarty turned off in the config files and turn it on in the view with the widget settings.
```php
echo MarkdownEditor::widget([
    'model' => $model, 
    'attribute' => 'markdown',
    'smarty' => true,
    'previewAction' => Url::to(['my/preview']),
]);
```
Then create an action in your controller and implement RBAC there. That way Smarty templates is off by default and you can
turn it on and control access to it in the Controller.
```php
class MyController extends Controller
{
    public function actionPreview()
    {
        $module = Yii::$app->getModule('markdown');
        if (\Yii::$app->user->can('smarty')) {
            $module->smarty = true;
            $module->smartyYiiApp = \Yii::$app->user->can('smartyYiiApp') ? true : false;
            $module->smartyYiiParams = Yii::$app->user->can('smartyYiiParams') ? true : false;
        }
        if (isset($_POST['source'])) {
            $output = (strlen($_POST['source']) > 0) ? Markdown::convert($_POST['source'], ['custom' => $module->customConversion]) : $_POST['nullMsg'];
        }
        echo Json::encode(HtmlPurifier::process($output));
    }
}
```
After saving the value to the database you can render it in your views with Markdown::convert(). For example if you save the Markdown field in the content column of the Post table you can use something like the following.
```php
$content = Post::find(['page_id'=>'myPage'])->one()->content;
echo HtmlPurifier::process(Markdown::convert($content, ['custom' => $module->customConversion]))
```

## License

**yii2-markdown** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.
