<?php

namespace mrssoft\sitemap;

use Yii;
use yii\helpers\Url;
use yii\web\Response;

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

            foreach ($this->urls() as $item)
            {
                $sitemap->addUrl(
                    isset($item['url']) ? Url::toRoute($item['url'], true) : Url::toRoute($item, true),
                    isset($item['change']) ? $item['change'] : Sitemap::DAILY,
                    isset($item['priority']) ? $item['priority'] : Sitemap::DEFAULT_PRIORITY,
                    isset($item['lastmod']) ? $item['lastmod'] : 0
                );
            }

            foreach ($this->models() as $model) {
                $obj = new $model['class'];
                if ($obj instanceof SitemapInterface) {
                    $sitemap->addModels(
                        $obj::sitemap()->all(),
                        isset($model['change']) ? $model['change'] : Sitemap::DAILY,
                        isset($model['priority']) ? $model['priority'] : Sitemap::DEFAULT_PRIORITY,
                        isset($model['lastmod']) ? $model['lastmod'] : Sitemap::LASTMOD_FIELD
                    );
                }
            }

            $xml = $sitemap->render();
            file_put_contents($cachePath, $xml);
        } else {
            $xml = file_get_contents($cachePath);
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->getResponse()
                  ->getHeaders()
                  ->set('Content-Type', 'text/xml; charset=utf-8');

        return $xml;
    }
}
