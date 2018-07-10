<?php

namespace Jikan\Request\Anime;

use Jikan\Request\RequestInterface;

class AnimeMoreInfoRequest implements RequestInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * AnimeMoreInfoRequest constructor.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('https://myanimelist.net/anime/%d/_/moreinfo', $this->id);
    }
}
