<?

namespace mrssoft\sitemap;

interface SitemapInterface
{
    /**
     * @return string
     */
    public function getSitemapUrl();


    /**
     * @return \yii\db\ActiveQuery
     */
    public static function sitemap();
}