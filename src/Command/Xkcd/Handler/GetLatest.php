<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Xkcd\Handler;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Plugin\Xkcd\Plugin;
use Room11\Jeeves\Command\Xkcd\Responder\Render;
use function Amp\call;

final class GetLatest
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
    public function handle(): Promise
    {
        return call(function () {
            $comic = yield $this->plugin->getLatest();

            yield (new Render($this->bot))->respond($comic);
        });
    }
}
