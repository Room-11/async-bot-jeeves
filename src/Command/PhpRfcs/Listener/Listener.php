<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\PhpRfcs\Listener;

use Amp\Promise;
use Amp\Success;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted;
use AsyncBot\Plugin\PhpRfcs\Plugin;
use Room11\Jeeves\Command\PhpRfcs\Handler\GetRfcsInVoting;

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
        if ($message->getContent() !== '!!rfcs') {
            return new Success();
        }

        return (new GetRfcsInVoting($this->bot, $this->plugin))->handle();
    }
}
