<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\OpenGrok\Handler;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Plugin\OpenGrok\Collection\SearchResults;
use AsyncBot\Plugin\OpenGrok\Plugin;
use Room11\Jeeves\Command\OpenGrok\Responder\Methods;
use function Amp\call;

final class FindMethod
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
    public function handle(string $method): Promise
    {
        return call(function () use ($method) {
            /** @var SearchResults $searchResults */
            $searchResults = yield $this->plugin->findMethod($method);

            yield (new Methods($this->bot))->respond($searchResults);
        });
    }
}
