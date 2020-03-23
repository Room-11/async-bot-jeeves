<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\PhpRfcs\Responder;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Node\Separator;
use AsyncBot\Core\Message\Node\Tag;
use AsyncBot\Core\Message\Node\Text;
use AsyncBot\Core\Message\Node\Url;
use AsyncBot\Plugin\PhpRfcs\ValueObject\Links;

final class Overview
{
    private Driver $bot;

    public function __construct(Driver $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return Promise<null>
     */
    public function respond(Links $links): Promise
    {
        if (!$links->count()) {
            return $this->bot->postMessage(
                (new Message())->appendNode(new Text('There are currently no RFCs in voting')),
            );
        }

        $message = (new Message())
            ->appendNode((new Tag())->appendNode(new Text('rfc-votes')))
            ->appendNode(new Text(' '))
        ;

        foreach ($links as $index => $link) {
            if ($index > 0) {
                $message->appendNode(new Separator());
            }

            $message
                ->appendNode(
                    (new Url((string) $link->getUri()))->appendNode(new Text($link->getTitle()))
                )
            ;
        }

        return $this->bot->postMessage($message);
    }
}
