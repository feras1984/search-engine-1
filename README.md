# Crawling Search Engine
In this project, we will mimic the searching operation done by normal user to get a bundle of websites. Then we will enter the available pages and extract some metadata.
In order to apply the simulation process we will:
1. Search the required keywords and extract the useful links.
2. For each link, get the metadata associated with it.

There are many libraries to make crawling using PHP, such as Guzzle, Guette, SimpleHtmlDom, or Panther.
In this application, we used Symphony Panther client as it is suitable for dynamic pages that uses ajax, axios libraries to get their data.
But Symphony panther needs chrome/firefox driver to act as a browser. We need to install the driver before beginning the panther application.

To configure Symphony Panther, We do the following:
1. Install chrome driver

`composer require --dev dbrekelmans/bdi && vendor/bin/bdi detect drivers`
2. Install the package that is designed to do the job.
`composer require awam/se`

The package awam/se is hosted in [`https://packagist.org/`](https://packagist.org/).

After installing the packages, we can create `index.php` file. If we want to find a keywork like `red`.
we can perform this snippet:
```angular2html

use HttpClient\PantherClient;

require "vendor/autoload.php";

/**
 * In order to try many HttpClients, such as (Guzzle, Goutte, SimpleHtmlDom, or Panther), we need loose coupling
 * between Search Engine and Http Clients, so we have used service container (Dependency Injection) in this case.
 * In this application, we used Symphony Panther client as it is suitable for dynamic pages that uses ajax, axios libraries to get their data.
 */


$httpClient = new HttpClient\PantherClient();

$client = new SearchEngine\SearchEngine($httpClient);
$client->setEngine('google.ae');
$results = $client->search(['red']);

while ($results->valid()) {
    var_dump("\n==================\n");
    var_dump($results->current());
    $results->next();
}
```

After that we can run the command in cmd:

`php index.php`

Note that $results is ArrayIterator that we can roam between the links.
The project can be easily expanded to accommodate other Http Clients
thanks to dependency injection made between SearchEngine and PantherClient.
