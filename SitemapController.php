<?php

namespace mrssoft\sitemap;

use yii\helpers\Url;

class SitemapController extends \yii\web\Controller
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
        return [];
    }

    public function urls()
    {
        return [];
    }

    public function actionIndex()
    {
        $cachePath = \Yii::$app->runtimePath . DIRECTORY_SEPARATOR . $this->cacheFilename;

        if (empty($this->cacheDuration) || !is_file($cachePath) || filemtime($cachePath) < time() - $this->cacheDuration) {
            $sitemap = new Sitemap();

            foreach ($this->urls() as $item) {

                $url = isset($item['url']) ? Url::toRoute($item['url'], true) : Url::toRoute($item, true);
                $change = isset($item['change']) ? $item['change'] : Sitemap::DAILY;
                $priority = isset($item['priority']) ? $item['priority'] : 0.8;
                $lastmod = isset($item['lastmod']) ? $item['lastmod'] : 0;

                $sitemap->addUrl($url, $change, $priority, $lastmod);
            }

            foreach ($this->models() as $model) {
                $obj = new $model['class'];
                if ($obj instanceof SitemapInterface) {

                    $list = $obj::sitemap()
                                ->all();
                    $change = isset($model['change']) ? $model['change'] : Sitemap::DAILY;
                    $priority = isset($model['priority']) ? $model['priority'] : 0.8;
                    $lastmod = isset($model['lastmod']) ? $model['lastmod'] : 0;

                    $sitemap->addModels($list, $change, $priority, $lastmod);
                }
            }

            $xml = $sitemap->render();
            file_put_contents($cachePath, $xml);
        } else {
            $xml = file_get_contents($cachePath);
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        \Yii::$app->getResponse()
                  ->getHeaders()
                  ->set('Content-Type', 'text/xml; charset=utf-8');
        return $xml;
    }
}
