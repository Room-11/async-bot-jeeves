<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\CustomCommand\Handler;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Core\Message\Parser;
use AsyncBot\Plugin\CustomMessageCommand\Plugin;
use Room11\Jeeves\Command\CustomCommand\Formatter\RegisteredCommand;
use function Amp\call;

final class RegisterCommand
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
    public function handle(string $command, string $message): Promise
    {
        return call(function () use ($command, $message) {
            $message = (new Parser())->parse($message);

            yield $this->plugin->registerNewCommand($command, $message);

            yield $this->bot->postMessage(
                (new RegisteredCommand())->format($command),
            );

            yield $this->bot->postMessage($message);
        });
    }
}
