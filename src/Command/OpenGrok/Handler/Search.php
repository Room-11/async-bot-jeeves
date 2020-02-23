<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\OpenGrok\Handler;

use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Plugin\OpenGrok\Collection\SearchResults;
use AsyncBot\Plugin\OpenGrok\Plugin;
use Room11\Jeeves\Command\OpenGrok\Responder\SearchResults as Responder;
use function Amp\call;

final class Search
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
    public function handle(string $macro): Promise
    {
        return call(function () use ($macro) {
            /** @var SearchResults $searchResults */
            $searchResults = yield $this->plugin->search($macro);

            yield (new Responder($this->bot))->respond($searchResults);
        });
    }
}
