<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Imdb\Handler;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Plugin\Imdb\Plugin;
use AsyncBot\Plugin\Imdb\ValueObject\Result\Title;
use Room11\Jeeves\Command\Imdb\Responder\Title as Responder;
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
            /** @var Title $title */
            $title = yield $this->getTitle($keywords);

            yield (new Responder($this->bot))->respond($title);
        });
    }

    /**
     * @return Promise<?Title>
     */
    private function getTitle(string $keywords): Promise
    {
        return call(function () use ($keywords) {
            try {
                return yield $this->plugin->searchByTitle($keywords);
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
