Sitemap Extension for Yii 2
===========================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mrssoft/yii2-sitemap "dev-master"
```

or add

```json
"mrssoft/yii2-sitemap": "dev-master"
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

    public function models()
    {
        return [
            [
                'class' => \app\models\Page::className(),
                'change' => Sitemap::MONTHLY,
                'priority' => 0.8,
                'lastmod' => 'updated_at',
            ]
        ];
    }

    public function urls()
    {
        return [
            [
                'url' => 'about/index',
                'change' => Sitemap::MONTHLY,
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
    public static function sitemap()
    {
        return self::find()->where('public=1');
    }

    /**
     * @return string
     */
    public function getSitemapUrl()
    {
        return  \yii\helpers\Url::toRoute(['page/view', 'url' => $this->url], true);
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
