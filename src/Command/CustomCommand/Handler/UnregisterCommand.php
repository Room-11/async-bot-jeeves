<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\CustomCommand\Handler;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Plugin\CustomMessageCommand\Plugin;
use Room11\Jeeves\Command\CustomCommand\Formatter\UnregisteredCommand;
use function Amp\call;

final class UnregisterCommand
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
    public function handle(string $command): Promise
    {
        return call(function () use ($command) {
            yield $this->plugin->unregisterNewCommand($command);

            yield $this->bot->postMessage(
                (new UnregisteredCommand())->format($command),
            );
        });
    }
}
