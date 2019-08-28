<?php

namespace mrssoft\sitemap;

class Sitemap
{
    const ALWAYS = 'always';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';
    const NEVER = 'never';
    const LASTMOD_FIELD = 'updated_at';
    const DEFAULT_PRIORITY = 0.8;

    protected $items = [];

    /**
     * @param $url
     * @param string $changeFreq
     * @param float $priority
     * @param int $lastMod
     */
    public function addUrl($url, $changeFreq, $priority, $lastMod)
    {
        if (in_array($url, $this->items)) {
            return;
        }

        $item = [
            'loc' => $url,
            'changefreq' => $changeFreq,
            'priority' => $priority
        ];

        if ($lastMod) {
            $item['lastmod'] = $this->dateToW3C($lastMod);
        }

        $this->items[$url] = $item;
    }

    /**
     * @param SitemapInterface[]|\yii\db\ActiveRecord $models
     * @param string $changeFreq
     * @param float $priority
     * @param $lastmod
     */

    public function addModels($models, $changeFreq, $priority, $lastmod)
    {
        foreach ($models as $model) {
            $url = $model->getSitemapUrl();
            if (in_array($url, $this->items) === false) {
                $item = [
                    'loc' => $url,
                    'changefreq' => $changeFreq,
                    'priority' => $priority
                ];

                if ($model->hasAttribute($lastmod)) {
                    $item['lastmod'] = $this->dateToW3C($model->getAttribute($lastmod));
                }

                $this->items[$url] = $item;
            }
        }
    }

    /**
     * @return string XML code
     */
    public function render()
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($this->items as $item) {
            $url = $dom->createElement('url');

            foreach ($item as $key => $value) {
                $elem = $dom->createElement($key);
                $elem->appendChild($dom->createTextNode($value));
                $url->appendChild($elem);
            }

            $urlset->appendChild($url);
        }
        $dom->appendChild($urlset);

        return $dom->saveXML();
    }

    protected function dateToW3C($date)
    {
        if (is_int($date)) {
            return date(DATE_W3C, $date);
        }

        return date(DATE_W3C, strtotime($date));
    }
}
