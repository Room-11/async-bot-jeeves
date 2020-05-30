<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\CustomCommand\Formatter;

use AsyncBot\Core\Message\Node\Code;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Node\Text;

final class RegisteredCommand
{
    public function format(string $command): Message
    {
        return (new Message())
            ->appendNode(new Text('I will now be listening to the command '))
            ->appendNode((new Code())->appendNode(new Text('!!' . $command)))
            ->appendNode(new Text(' and will respond with:'))
        ;
    }
}
