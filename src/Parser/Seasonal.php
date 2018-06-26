<?php

namespace Jikan\Parser;

use Jikan\Helper\JString;
use Jikan\Model;
use Symfony\Component\DomCrawler\Crawler;

class Seasonal implements ParserInterface
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * Seasonal constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function getModel(): Model\Seasonal
    {
        return Model\Seasonal::fromParser($this);
    }

    /**
     * @return array|Model\SeasonalAnime[]
     * @throws \RuntimeException
     */
    public function getSeasonalAnime(): array
    {
        return $this->crawler
            ->filter('div.seasonal-anime')
            ->each(
                function (Crawler $animeCrawler) {
                    return (new AnimeCard($animeCrawler))->getModel();
                }
            );
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSeason(): string
    {
        $season = $this->crawler->filter('div.navi-seasonal a.on')->text();

        return JString::cleanse($season);
    }
}