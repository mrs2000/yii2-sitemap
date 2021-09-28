<?php

namespace mrssoft\sitemap;

interface SitemapInterface
{
    /**
     * @return string
     */
    public function getSitemapUrl(): string;


    /**
     * @return \yii\db\ActiveQuery
     */
    public static function sitemap(): \yii\db\ActiveQuery;
}