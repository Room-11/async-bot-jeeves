<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Wikipedia\Handler;

use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Plugin\Wikipedia\Collection\Search\OpenSearchResults;
use AsyncBot\Plugin\Wikipedia\Plugin;
use Room11\Jeeves\Command\Wikipedia\Responder\SearchResults as Responder;
use function Amp\call;

final class SearchByTitle
{
    private Driver $bot;

    private Plugin $plugin;

    public function __construct(Driver $bot, Plugin $plugin)
    {
        $this->bot    = $bot;
        $this->plugin = $plugin;
    }

    /**
     * @return Promise<null>
     */
    public function handle(string $keywords): Promise
    {
        return call(function () use ($keywords) {
            /** @var OpenSearchResults $searchResults */
            $searchResults = yield $this->getTitle($keywords);

            yield (new Responder($this->bot))->respond($searchResults);
        });
    }

    /**
     * @return Promise<?OpenSearchResults>
     */
    private function getTitle(string $keywords): Promise
    {
        return call(function () use ($keywords) {
            try {
                return yield $this->plugin->openSearch($keywords);
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
