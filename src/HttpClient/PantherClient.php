<?php
namespace HttpClient;
require 'vendor/autoload.php';
require 'HttpCustomClientContract.php';

class PantherClient implements HttpCustomClientContract
{
    private $link;
    private $method;
    private $selector;
    private $httpClient;
    /**
     * this class accepts a method, a link, and a selector.
     */
    public function __construct()
    {
        $this->httpClient = \Symfony\Component\Panther\Client::createChromeClient();
    }

    public function setParameters($method ,$link, $selector = ''){
        $this->method = $method;
        $this->link = $link;
        $this->selector = $selector;
    }

    /**
     * This function is used to return panther client with specific method, link, and selector.
     */

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function getClient()
    {
        try{
            $crawler = $this->httpClient->request($this->method, $this->link);
            if ($this->selector != ''){
                $this->httpClient->waitFor($this->selector);
            }
            return $crawler;
        } catch (\Exception $exception){
            return false;
//            return new \Symfony\Component\Panther\DomCrawler\Crawler();
        }
    }
}