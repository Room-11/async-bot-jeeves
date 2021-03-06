<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Xkcd\Listener;

use Amp\Promise;
use Amp\Success;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted;
use AsyncBot\Plugin\Xkcd\Plugin;
use Room11\Jeeves\Command\Xkcd\Handler\Find;
use Room11\Jeeves\Command\Xkcd\Handler\GetById;
use Room11\Jeeves\Command\Xkcd\Handler\GetLatest;

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
        if ($message->getContent() === '!!xkcd') {
            return (new GetLatest($this->bot, $this->plugin))->handle();
        }

        if (strpos($message->getContent(), '!!xkcd') !== 0) {
            return new Success();
        }

        if (preg_match('~^!!xkcd (\d+)$~', $message->getContent(), $matches) === 1) {
            return (new GetById($this->bot, $this->plugin))->handle((int) $matches[1]);
        }

        preg_match('~^!!xkcd (.+)~', $message->getContent(), $matches);

        return (new Find($this->bot, $this->plugin))->handle($matches[1]);
    }
}
