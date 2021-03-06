<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Wikipedia\Listener;

use Amp\Promise;
use Amp\Success;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted;
use AsyncBot\Plugin\Wikipedia\Plugin;
use Room11\Jeeves\Command\Wikipedia\Handler\SearchByTitle;

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
        if (strpos($message->getContent(), '!!wiki ') !== 0) {
            return new Success();
        }

        preg_match('~^!!wiki (.+)~', $message->getContent(), $matches);

        return (new SearchByTitle($this->bot, $this->plugin))->handle($matches[1]);
    }
}
