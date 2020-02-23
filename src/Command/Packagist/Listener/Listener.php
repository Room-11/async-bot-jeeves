<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Packagist\Listener;

use Amp\Promise;
use Amp\Success;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted;
use AsyncBot\Plugin\PackagistFinder\Plugin;
use Room11\Jeeves\Command\Packagist\Handler\GetByPackageName;
use Room11\Jeeves\Command\Packagist\Handler\SearchForPackage;

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
        if (strpos($message->getContent(), '!!packagist ') !== 0) {
            return new Success();
        }

        if (preg_match('~^!!packagist ([^/]+/[^/]+)$~', $message->getContent(), $matches) === 1) {
            return (new GetByPackageName($this->bot, $this->plugin))->handle($matches[1]);
        }

        preg_match('~^!!packagist (.+)$~', $message->getContent(), $matches);

        return (new SearchForPackage($this->bot, $this->plugin))->handle($matches[1]);
    }
}
