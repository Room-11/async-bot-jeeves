<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\CustomCommand\Listener;

use Amp\Promise;
use Amp\Success;
use AsyncBot\Core\Driver;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted;
use AsyncBot\Plugin\CustomMessageCommand\Plugin;
use Room11\Jeeves\Command\CustomCommand\Handler\RegisterCommand;
use Room11\Jeeves\Command\CustomCommand\Handler\UnregisterCommand;
use Room11\Jeeves\Command\Imdb\Handler\SearchById;
use Room11\Jeeves\Command\Imdb\Handler\SearchByTitle;
use function Amp\call;

final class Listener implements MessagePosted
{
    private const PATTERN_REGISTER_COMMAND   = '~^!!command (?P<command>\S+) (?P<message>.+)$~';
    private const PATTERN_UNREGISTER_COMMAND = '~^!!uncommand (?P<command>\S+)$~';

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
    public function __invoke(Message $message): Promise
    {
        if ($this->isRegisterCommandRequest($message)) {
            return $this->handleRegisterCommand($message);
        }

        if ($this->isUnregisterCommandRequest($message)) {
            return $this->handleUnregisterCommand($message);
        }

        return $this->handleCommand($message);
    }

    private function isRegisterCommandRequest(Message $message): bool
    {
        return preg_match(self::PATTERN_REGISTER_COMMAND, html_entity_decode($message->getContent())) === 1;
    }

    private function handleRegisterCommand(Message $message): Promise
    {
        preg_match(self::PATTERN_REGISTER_COMMAND, html_entity_decode($message->getContent()), $matches);

        return (new RegisterCommand($this->bot, $this->plugin))->handle($matches['command'], $matches['message']);
    }

    private function isUnregisterCommandRequest(Message $message): bool
    {
        return preg_match(self::PATTERN_UNREGISTER_COMMAND, $message->getContent()) === 1;
    }

    private function handleUnregisterCommand(Message $message): Promise
    {
        preg_match(self::PATTERN_UNREGISTER_COMMAND, $message->getContent(), $matches);

        return (new UnregisterCommand($this->bot, $this->plugin))->handle($matches['command']);
    }

    /**
     * @return Promise<bool>
     */
    private function handleCommand(Message $message): Promise
    {
        return call(function () use ($message) {
            if (preg_match('~^!!(?P<command>\S+)$~', $message->getContent(), $matches) !== 1) {
                return new Success();
            }

            if (!yield $this->plugin->isCommand($matches['command'])) {
                return new Success();
            }

            return $this->bot->postMessage(yield $this->plugin->getMessage($matches['command']));
        });
    }
}
