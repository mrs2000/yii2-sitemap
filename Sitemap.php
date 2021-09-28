<?php

namespace mrssoft\sitemap;

class Sitemap
{
    public const ALWAYS = 'always';
    public const HOURLY = 'hourly';
    public const DAILY = 'daily';
    public const WEEKLY = 'weekly';
    public const MONTHLY = 'monthly';
    public const YEARLY = 'yearly';
    public const NEVER = 'never';

    public const LASTMOD_FIELD = 'updated_at';

    public const DEFAULT_PRIORITY = 0.8;

    protected $items = [];

    /**
     * @param string $url
     * @param string $changeFreq
     * @param float $priority
     * @param string $lastMod
     */
    public function addUrl(string $url, string $changeFreq, float $priority, string $lastMod): void
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
     * @param string $lastmod
     */

    public function addModels(array $models, string $changeFreq, float $priority, string $lastmod): void
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
    public function render(bool $enablePriority, bool $enablechangeFreq): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'https://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($this->items as $item) {
            $url = $dom->createElement('url');

            foreach ($item as $key => $value) {
                if ($key === 'priority' && $enablePriority === false) {
                    continue;
                }
                if ($key === 'changefreq' && $enablechangeFreq === false) {
                    continue;
                }
                $elem = $dom->createElement($key);
                $elem->appendChild($dom->createTextNode($value));
                $url->appendChild($elem);
            }

            $urlset->appendChild($url);
        }
        $dom->appendChild($urlset);

        return $dom->saveXML();
    }

    protected function dateToW3C($date): string
    {
        if (is_int($date)) {
            return date(DATE_W3C, $date);
        }

        return date(DATE_W3C, strtotime($date));
    }
}
