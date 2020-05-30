<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\CustomCommand\Formatter;

use AsyncBot\Core\Message\Node\Code;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Node\Text;

final class UnregisteredCommand
{
    public function format(string $command): Message
    {
        return (new Message())
            ->appendNode(new Text('I will now stop listening to the command '))
            ->appendNode((new Code())->appendNode(new Text('!!' . $command)))
        ;
    }
}
