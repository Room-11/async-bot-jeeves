<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\OpenGrok\Listener;

use Amp\Promise;
use Amp\Success;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted;
use AsyncBot\Plugin\OpenGrok\Plugin;
use Room11\Jeeves\Command\OpenGrok\Handler\FindFunction;
use Room11\Jeeves\Command\OpenGrok\Handler\FindMacro;
use Room11\Jeeves\Command\OpenGrok\Handler\FindMethod;
use Room11\Jeeves\Command\OpenGrok\Handler\Search;

final class Listener implements MessagePosted
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
    public function __invoke(Message $message): Promise
    {
        if (preg_match('~^!!lxrfunction (.+)$~', $message->getContent()) === 1) {
            return $this->findFunction($message->getContent());
        }

        if (preg_match('~^!!lxrmethod (.+)$~', $message->getContent()) === 1) {
            return $this->findMethod($message->getContent());
        }

        if (preg_match('~^!!lxrmacro (.+)$~', $message->getContent()) === 1) {
            return $this->findMacro($message->getContent());
        }

        if (preg_match('~^!!lxr (.+)$~', $message->getContent()) === 1) {
            return $this->search($message->getContent());
        }

        return new Success();
    }

    private function findFunction(string $command): Promise
    {
        return (new FindFunction($this->bot, $this->plugin))->handle(
            $this->getParameter($command),
        );
    }

    private function findMethod(string $command): Promise
    {
        return (new FindMethod($this->bot, $this->plugin))->handle(
            $this->getParameter($command),
        );
    }

    private function findMacro(string $command): Promise
    {
        return (new FindMacro($this->bot, $this->plugin))->handle(
            $this->getParameter($command),
        );
    }

    private function search(string $command): Promise
    {
        return (new Search($this->bot, $this->plugin))->handle(
            $this->getParameter($command),
        );
    }

    private function getParameter(string $command): string
    {
        preg_match('~^!![^ ]+ (.+)$~', $command, $matches);

        return str_replace(['<i>', '</i>'], '*', $matches[1]);
    }
}
