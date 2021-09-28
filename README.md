Sitemap Extension for Yii 2
===========================

[![Latest Stable Version](https://img.shields.io/packagist/v/mrssoft/yii2-sitemap.svg)](https://packagist.org/packages/mrssoft/yii2-sitemap)
![PHP](https://img.shields.io/packagist/php-v/mrssoft/yii2-sitemap.svg)
![Github](https://img.shields.io/github/license/mrs2000/yii2-sitemap.svg)
![Total Downloads](https://img.shields.io/packagist/dt/mrssoft/yii2-sitemap.svg)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mrssoft/yii2-sitemap "^2.0"
```

or add

```
"mrssoft/yii2-sitemap": "^2.0"
```

to the require section of your composer.json.


Usage
-----

Create controller `SitemapController.php`

```php
<?php

namespace app\controllers;

use \mrssoft\sitemap\Sitemap;

class SitemapController extends \mrssoft\sitemap\SitemapController
{
    /**
     * @var int Cache duration, set null to disabled
     */
    protected $cacheDuration = 43200; // default 12 hour

    /**
     * @var string Cache filename
     */
    protected $cacheFilename = 'sitemap.xml';
    
    protected $enablePriority = false;

    protected $enableChangeFreq = false;    

    public function models(): array
    {
        return [
            [
                'class' => \app\models\Page::class,
                'change' => Sitemap::MONTHLY,
                'priority' => 0.8,
                'lastmod' => 'updated_at',
            ]
        ];
    }

    public function urls(): array
    {
        return [
            [
                'url' => ['about/index'],
                'priority' => 0.8
            ]
        ];
    }
}
```

Add to your models interface `\mrssoft\sitemap\SitemapInterface`

```php
<?php
namespace app\models;

class Page extends \yii\db\ActiveRecord implements \mrssoft\sitemap\SitemapInterface
{
    ...
    
    /**
     * @return \yii\db\ActiveQuery
     */        
    public static function sitemap(): \yii\db\ActiveQuery
    {
        return self::find()->where(['public' => 1]);
    }

    /**
     * @return string
     */
    public function getSitemapUrl(): string
    {
        return \yii\helpers\Url::toRoute(['page/view', 'url' => $this->url], true);
    }    
}
```

Add to config url rule.

```php
'components' => [
    'urlManager' => [
        'rules' => [
            ...
            [
                'pattern' => 'sitemap', 
                'route' => 'sitemap/index', 
                'suffix' => '.xml'
            ],
            ...
        ]
    ],
```
