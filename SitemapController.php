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

    /**
     * Add priority
     * @var bool
     */
    protected $enablePriority = true;

    /**
     * Add changeFreq
     * @var bool
     */
    protected $enableChangeFreq = true;

    public function models(): array
    {
        return [];
    }

    public function urls(): array
    {
        return [];
    }

    public function actionIndex()
    {
        $cachePath = Yii::$app->runtimePath . DIRECTORY_SEPARATOR . $this->cacheFilename;

        if ($this->cacheDuration === null || is_file($cachePath) === false || filemtime($cachePath) < time() - $this->cacheDuration) {
            $xml = $this->generateXml();
            if ($this->cacheDuration) {
                file_put_contents($cachePath, $xml);
            }
        } else {
            $xml = file_get_contents($cachePath);
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->getResponse()
                 ->getHeaders()
                 ->set('Content-Type', 'text/xml; charset=utf-8');

        return $xml;
    }

    private function generateXml(): string
    {
        $sitemap = new Sitemap();

        foreach ($this->urls() as $item) {
            $sitemap->addUrl(
                isset($item['url']) ? Url::toRoute($item['url'], true) : Url::toRoute($item, true),
                $item['change'] ?? Sitemap::DAILY,
                $item['priority'] ?? Sitemap::DEFAULT_PRIORITY,
                $item['lastmod'] ?? 0
            );
        }

        foreach ($this->models() as $model) {
            $obj = new $model['class'];
            if ($obj instanceof SitemapInterface) {
                $models = $obj::sitemap()
                              ->all();
                $sitemap->addModels(
                    $models,
                    $model['change'] ?? Sitemap::DAILY,
                    $model['priority'] ?? Sitemap::DEFAULT_PRIORITY,
                    $model['lastmod'] ?? Sitemap::LASTMOD_FIELD
                );
            }
        }

        return $sitemap->render($this->enablePriority, $this->enableChangeFreq);
    }
}
