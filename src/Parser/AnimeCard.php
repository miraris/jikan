<?php

namespace Jikan\Parser;

use Jikan\Helper\JString;
use Jikan\Model;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AnimeCard
 *
 * @package Jikan\Parser
 */
class AnimeCard implements ParserInterface
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * AnimeCard constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * @return Model\SeasonalAnime
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getModel(): Model\SeasonalAnime
    {
        return Model\SeasonalAnime::parseSeasonalAnime($this);
    }

    /**
     * @return Model\MalUrl[]
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getProducer(): array
    {
        return $this->crawler
            ->filterXPath('//span[contains(@class, "producer")]/a')
            ->each(
                function (Crawler $crawler) {
                    return (new MalUrlParser($crawler))->getModel();
                }
            );
    }

    /**
     * @return int|null
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getEpisodes(): ?int
    {

        $eps = $this->crawler->filterXPath('//div[contains(@class, "eps")]')->text();
        $eps = JString::cleanse($eps);
        str_replace(' eps', '', $eps);

        return $eps === '?' ? null : (int)$eps;
    }

    /**
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getSource(): string
    {
        return $this->crawler->filterXPath('//span[contains(@class, "source")]')->text();
    }

    /**
     * @return array|Model\MalUrl[]
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getGenres(): array
    {
        return $this->crawler->filterXPath('//span[contains(@class, "genre")]/a')
            ->each(
                function (Crawler $crawler) {
                    return (new MalUrlParser($crawler))->getModel();
                }
            );
    }

    /**
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getTitle(): string
    {
        return $this->crawler->filterXPath('//p[contains(@class,"title-text")]/a')->text();
    }

    /**
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getDescription(): string
    {
        return $this->crawler->filterXPath('//div[contains(@class, "synopsis")]/span')->text();
    }

    /**
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getType(): string
    {
        $text = $this->crawler->filterXPath('//div[contains(@class, "info")]')->text();
        $text = JString::cleanse($text);
        preg_match('/^([\w\.]+)/', $text, $matches);

        return $matches[1];
    }

    /**
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getAirDates(): string
    {
        return JString::cleanse($this->crawler->filterXPath('//span[contains(@class, "remain-time")]')->text());
    }

    /**
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getMembers(): int
    {
        $count = $this->crawler->filterXPath('//div[contains(@class, "scormem")]/span')->text();
        $count = JString::cleanse($count);

        return (int)str_replace(',', '', $count);
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getAnimeId(): int
    {
        preg_match('#https?://myanimelist.net/anime/(\d+)#', $this->getAnimeUrl(), $matches);

        return (int)$matches[1];
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getAnimeUrl(): string
    {
        return $this->crawler->filterXPath('//div[contains(@class, "title")]/p/a')->attr('href');
    }

    /**
     * @return string|null
     * @throws \RuntimeException
     */
    public function getAnimeImage(): ?string
    {
        return $this->crawler->filterXPath('//div[contains(@class, "image")]/img')->first()->attr('src');
    }

    /**
     * @return float|null
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getAnimeScore(): ?float
    {
        $score = JString::cleanse($this->crawler->filterXPath('//span[contains(@class, "score")]')->text());
        if ($score === 'N/A') {
            return null;
        }

        return (float)$score;
    }

    /**
     * @return null|string[]
     * @throws \RuntimeException
     */
    public function getLicensors(): ?array
    {
        $licensors = $this->crawler->filterXPath('//p[contains(@class, "licensors")]');
        if (!$licensors->count()) {
            return null;
        }
        $licensors = JString::cleanse($licensors->attr('data-licensors'));
        $licensors = explode(',', $licensors);

        return array_filter($licensors);
    }

    /**
     * @return bool
     */
    public function isR18(): bool
    {
        $classes = explode(' ', $this->crawler->attr('class'));

        return \in_array('r18', $classes, true);
    }

    /**
     * @return bool
     */
    public function isKids(): bool
    {
        $classes = explode(' ', $this->crawler->attr('class'));

        return \in_array('kids', $classes, true);
    }

    /**
     * @return bool
     */
    public function isContinuing(): bool
    {
        return strpos(
                $this->crawler->parents()->text(),
                '(Continuing)'
            ) !== false;
    }
}