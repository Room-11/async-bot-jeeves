<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Packagist\Handler;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Plugin\PackagistFinder\Collection\SearchResults;
use AsyncBot\Plugin\PackagistFinder\Plugin;
use Room11\Jeeves\Command\Packagist\Responder\Search as Responder;
use function Amp\call;

final class SearchForPackage
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
    public function handle(string $keyword): Promise
    {
        return call(function () use ($keyword) {
            /** @var SearchResults $searchResults */
            $searchResults = yield $this->search($keyword);

            yield (new Responder($this->bot))->respond($searchResults);
        });
    }

    /**
     * @return Promise<SearchResults>
     */
    private function search(string $keyword): Promise
    {
        return call(function () use ($keyword) {
            try {
                return yield $this->plugin->searchByName($keyword);
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
