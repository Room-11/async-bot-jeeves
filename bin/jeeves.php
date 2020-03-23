#!/usr/bin/env php
<?php declare(strict_types=1);

namespace Room11\Jeeves\Bin;

use Amp\Http\Client\HttpClientBuilder;
use AsyncBot\Core\Http\Client;
use AsyncBot\Core\Logger\Factory as LoggerFactory;
use AsyncBot\Core\Manager;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Factory as StackOverflowChatDriverFactory;
use AsyncBot\Plugin\GitHubStatus\Parser\Html;
use AsyncBot\Plugin\GitHubStatus\Plugin as GitHubStatusPlugin;
use AsyncBot\Plugin\GitHubStatus\Retriever\Http;
use AsyncBot\Plugin\GitHubStatus\Storage\InMemoryRepository;
use AsyncBot\Plugin\GoogleSearch\Plugin as GooglePlugin;
use AsyncBot\Plugin\Imdb\Plugin as ImdbPlugin;
use AsyncBot\Plugin\Imdb\ValueObject\ApiKey;
use AsyncBot\Plugin\LinuxManualPages\Plugin as LinuxManualPagesPlugin;
use AsyncBot\Plugin\OpenGrok\Plugin as OpenGrokPlugin;
use AsyncBot\Plugin\PackagistFinder\Plugin as PackagistFinderPlugin;
use AsyncBot\Plugin\PhpBugs\Parser\Html as PhpBugsParser;
use AsyncBot\Plugin\PhpBugs\Plugin as PhpBugsPlugin;
use AsyncBot\Plugin\PhpBugs\Retriever\GetAllBugs;
use AsyncBot\Plugin\PhpBugs\Storage\InMemoryRepository as PhpBugsStorage;
use AsyncBot\Plugin\PhpRfcs\Plugin as PhpRfcsPlugin;
use AsyncBot\Plugin\Wikipedia\Plugin as WikipediaPlugin;
use AsyncBot\Plugin\WordOfTheDay\Plugin as WordOfTheDayPlugin;
use Room11\Jeeves\Command\Google\Listener\Listener as GoogleListener;
use Room11\Jeeves\Command\Imdb\Listener\Listener as ImdbCommandListener;
use Room11\Jeeves\Command\Man\Listener\Listener as ManListener;
use Room11\Jeeves\Command\OpenGrok\Listener\Listener as OpenGrokListener;
use Room11\Jeeves\Command\Packagist\Listener\Listener as PackagistFinderListener;
use Room11\Jeeves\Command\PhpRfcs\Listener\Listener as PhpRfcsListener;
use Room11\Jeeves\Command\Wikipedia\Listener\Listener as WikipediaListener;
use Room11\Jeeves\Command\WordOfTheDay\Listener\Listener as WordOfTheDayCommandListener;
use Room11\Jeeves\Command\Xkcd\Listener\Listener as XkcdListener;
use Room11\Jeeves\Listener\OutputNewPhpBugs;
use Room11\Jeeves\Listener\OutputGitHubStatusChange;
use AsyncBot\Plugin\Xkcd\Plugin as XkcdPlugin;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Set up logger
 */
$logger = LoggerFactory::buildConsoleLogger();

/**
 * Set up the HTTP client
 */
$httpClient = new Client(HttpClientBuilder::buildDefault());

/**
 * Get the configuration
 */
$configuration = require_once __DIR__ . '/../config.php';

/**
 * Set up bot(s)
 */
$stackOverflowChatBot = (new StackOverflowChatDriverFactory(
    new Credentials(
        $configuration['drivers'][Driver::class]['username'],
        $configuration['drivers'][Driver::class]['password'],
        $configuration['drivers'][Driver::class]['roomUrl'],
    ),
))->build();

/**
 * Set up plugin(s)
 */
$imdbPlugin         = new ImdbPlugin($httpClient, new ApiKey($configuration['apis']['omdbApiKey']));
$wordOfTheDayPlugin = new WordOfTheDayPlugin($httpClient);
$packagistPlugin    = new PackagistFinderPlugin($httpClient);
$openGrokPlugin     = new OpenGrokPlugin($httpClient);
$linuxManPlugin     = new LinuxManualPagesPlugin($httpClient);
$wikipediaPlugin    = new WikipediaPlugin($httpClient);
$googlePlugin       = new GooglePlugin($httpClient);
$xkcdPlugin         = new XkcdPlugin($httpClient, $googlePlugin);
$phpRfcsPlugin      = new PhpRfcsPlugin($httpClient);

/**
 * Set up runnable plugin(s)
 */
$gitHubStatusPlugin = new GitHubStatusPlugin($logger, new Http($httpClient, new Html()), new InMemoryRepository());
$phpBugsPlugin      = new PhpBugsPlugin($logger, new GetAllBugs($httpClient, new PhpBugsParser()), new PhpBugsStorage(), new \DateInterval('PT1M'));

/**
 * Register for events
 */
$gitHubStatusPlugin->onStatusChange(new OutputGitHubStatusChange($stackOverflowChatBot));
$phpBugsPlugin->onNewBugs(new OutputNewPhpBugs($stackOverflowChatBot));

/**
 * Add listeners for commands
 */
$stackOverflowChatBot->onNewMessage(new ImdbCommandListener($stackOverflowChatBot, $imdbPlugin));
$stackOverflowChatBot->onNewMessage(new WordOfTheDayCommandListener($stackOverflowChatBot, $wordOfTheDayPlugin));
$stackOverflowChatBot->onNewMessage(new PackagistFinderListener($stackOverflowChatBot, $packagistPlugin));
$stackOverflowChatBot->onNewMessage(new OpenGrokListener($stackOverflowChatBot, $openGrokPlugin));
$stackOverflowChatBot->onNewMessage(new ManListener($stackOverflowChatBot, $linuxManPlugin));
$stackOverflowChatBot->onNewMessage(new WikipediaListener($stackOverflowChatBot, $wikipediaPlugin));
$stackOverflowChatBot->onNewMessage(new GoogleListener($stackOverflowChatBot, $googlePlugin));
$stackOverflowChatBot->onNewMessage(new XkcdListener($stackOverflowChatBot, $xkcdPlugin));
$stackOverflowChatBot->onNewMessage(new PhpRfcsListener($stackOverflowChatBot, $phpRfcsPlugin));

/**
 * Run the bot minions
 */
(new Manager($logger))
    ->registerBot($stackOverflowChatBot)
    ->registerPlugin($gitHubStatusPlugin)
    ->registerPlugin($phpBugsPlugin)
    ->run()
;
