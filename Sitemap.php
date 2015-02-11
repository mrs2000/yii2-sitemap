<?
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

    protected $items = [];

    /**
     * @param $url
     * @param string $changeFreq
     * @param float $priority
     * @param int $lastMod
     */
    public function addUrl($url, $changeFreq = self::DAILY, $priority = 0.5, $lastMod = 0)
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
     */
    public function addModels($models, $changeFreq = self::DAILY, $priority = 0.5)
    {
        foreach ($models as $model) {
            $url = $model->getSitemapUrl();
            if (!in_array($url, $this->items)) {
                $item = [
                    'loc' => $url,
                    'changefreq' => $changeFreq,
                    'priority' => $priority
                ];

                if ($model->hasAttribute('date')) {
                    $item['lastmod'] = $this->dateToW3C($model->getAttribute('date'));
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
        } else {
            return date(DATE_W3C, strtotime($date));
        }
    }
}