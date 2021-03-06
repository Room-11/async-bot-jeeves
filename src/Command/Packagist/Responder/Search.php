<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Packagist\Responder;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Node\Text;
use AsyncBot\Plugin\PackagistFinder\Collection\SearchResults;
use Room11\Jeeves\Command\Packagist\Formatter\SearchResult;
use function Amp\call;

final class Search
{
    private Driver $bot;

    public function __construct(Driver $bot)
    {
        $this->bot = $bot;
    }

    public function respond(SearchResults $searchResults): Promise
    {
        return call(function () use ($searchResults) {
            $headingMessage = (new Message())
                ->appendNode(new Text(sprintf('Total number of search results: %d', $searchResults->getTotalNumberOfResults())))
            ;

            if ($searchResults->getTotalNumberOfResults() > 5) {
                $headingMessage
                    ->appendNode(new Text('. '))
                    ->appendNode(new Text('Showing the first 5 results.'))
                ;
            }

            yield $this->bot->postMessage($headingMessage);

            foreach ($searchResults as $index => $searchResult) {
                if ($index === 5) {
                    break;
                }

                yield $this->bot->postMessage((new SearchResult())->format($searchResult));
            }
        });
    }
}
