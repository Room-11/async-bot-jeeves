<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\PhpRfcs\Handler;

use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Plugin\PhpRfcs\ValueObject\Links;
use AsyncBot\Plugin\PhpRfcs\Plugin;
use Room11\Jeeves\Command\PhpRfcs\Responder\Overview as Responder;
use function Amp\call;

final class GetRfcsInVoting
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
            /** @var Links $links */
            $links = yield $this->plugin->getRfcsInVoting();

            yield (new Responder($this->bot))->respond($links);
        });
    }
}
