<?php declare(strict_types=1);

namespace Room11\Jeeves\Command\Packagist\Responder;

use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Plugin\PackagistFinder\ValueObject\Package as PackageInformation;
use Room11\Jeeves\Command\Packagist\Formatter\BasicInformation;
use function Amp\call;

final class Package
{
    private Driver $bot;

    public function __construct(Driver $bot)
    {
        $this->bot = $bot;
    }

    public function respond(PackageInformation $package): Promise
    {
        return call(function () use ($package) {
            yield $this->bot->postMessage((new BasicInformation())->format($package));
        });
    }
}
