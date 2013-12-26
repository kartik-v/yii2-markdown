 yii2-markdown
==============

This module provides Markdown Editing and Conversion utilities for Yii Framework 2.0. It implements markdown conversion using [PHP Markdown Extra](http://michelf.ca/projects/php-markdown/extra/) and [PHP SmartyPants](http://michelf.ca/projects/php-smartypants/). It includes an enhanced customized Markdown Editor Widget for markdown editing and preview at runtime. This widget is styled using Twitter Bootstrap 3.0.

### MarkdownConverter
[```VIEW DEMO```](http://demos.krajee.com/markdown-details/markdown-converter)  
This is a markdown converter class that extends [Yii's Markdown conversion](https://github.com/yiisoft/yii2/blob/master/framework/yii/helpers/Markdown.php) helper with advanced functionality. The converter uses [PHP Markdown Extra](http://michelf.ca/projects/php-markdown/extra/) and [PHP Smarty Pants](http://michelf.ca/projects/php-smartypants/) for processing Markdown conversion to HTML. It also supports configurable custom conversion processing of patterns for styling your own flavour of Markdown to some extent.

### MarkdownEditor
[```VIEW DEMO```](http://demos.krajee.com/markdown-details/markdown-editor)  
This is an advanced markdown input widget with configurable options. It is styled using Twitter Bootstrap 3.0. Key features available with this widget are:

1. Implements a configurable toolbar for live editing from the editor
2. Toggle live preview of Markdown formatted text as HTML
3. Toggle editor for full screen editing
4. Implements PHP Markdown Extra and PHP Smarty Pants functionality as provided by the MarkdownConverter.
5. Uses Twitter Bootstrap 3.0 styling wherever possible with inbuilt Yii 2.0 ActiveField functionality.
6. Allows saving/exporting of the text-editor contents as Text or HTML
7. Configurable header, footer, and input options.

### Demo
You can see a [demonstration here](http://demos.krajee.com/markdown) on usage of these functions with documentation and examples.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

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
	/* your other modules */
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
		
		// whether to use PHP SmartyPants to process Markdown output
		'smartyPants' => true
		
	]
];
```

### MarkdownConverter
```php
use kartik\markdown\MarkdownConverter;

// default call
echo MarkdownConverter::process($content);

// with custom post processing
echo MarkdownConverter::process($content, ['custom' => [
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

## License

**yii2-markdown** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.
