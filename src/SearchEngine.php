<?php

namespace SearchEngine;

use ArrayObject;
use HttpClient\HttpCustomClientContract;

class SearchEngine
{
    private $httpClient;
    private $engine = 'google.com';
    private $keywords;

    public function __construct(HttpCustomClientContract $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param $keywords
     * @return array|void
     * basicFunction is used to extract links from kewords
     */

    private function basicSearch($keywords)
    {
        $kw = '';
        $links = [];
//        -----------------------------------
        $length = count($keywords);
        foreach ($keywords as $k => $keyword) {
            $kw = $kw . $keyword;
            if ($k < $length - 1) $kw = $kw . '+';
        }
        $url = 'https://www.' . $this->engine . '/search?q=' . $kw;
//    1. Perform Basic Search:
        $this->httpClient->setParameters('GET', $url, 'div#search div div');
        $crawler = $this->httpClient->getClient();
        try {
            if ($crawler){
                $crawler = $crawler->filter('div[style="width:652px"] div div div div a, div[style="width:600px"] div div div a');
                $crawler->each(function ($node) use (&$links) {
                    $links[] = $node->attr('href');
                });
            }
            else{
                throw new Exception();
            }
            return $links;
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
        }
    }

    /**
     * @param $links
     * @return ArrayObject
     * refineSearch is used to clean the links that are obtained from basic search (/search?q, google translate, ...etc).
     * Then we remove duplicated domains in the search!
     */

    private function refineSearch($links): ArrayObject
    {
//    2. Refine the search results (Chopping the results, eliminate duplications, return only first 5 pages):
        $refinedLinks = new ArrayObject($links);
        $iterator = $refinedLinks->getIterator();

//    Remove unwanted links (Long links, search links, Google Translate links):
        while ($iterator->valid()){
            if (str_contains($iterator->current(), ':http') or str_contains($iterator->current(), '=http')
                or str_contains($iterator->current(), '/search?q') or str_contains($iterator->current(), 'translate?hl')){
                unset($refinedLinks[$iterator->key()]);
            }
            else{
                $iterator->next();
            }
        }

//    Remove Duplicated domains:
        $loop = $refinedLinks->getIterator();
        $iterator->rewind();
        while($loop->valid()){
            while ($iterator->valid()){
                if(str_contains($iterator->current(), $loop->current()) and strcmp($iterator->current(), $loop->current()) != 0){
                    unset($refinedLinks[$iterator->key()]);
                }
                else{
                    $iterator->next();
                }
            }
            $iterator->rewind();
            $loop->next();
        }

        return $refinedLinks;
    }

    private function getMetaData($key, $link){
        $this->httpClient->setParameters('GET', $link, 'head');
        $crawler = $this->httpClient->getClient();
        if ($crawler){
            preg_match_all(
                "/<meta[^>]+(http\-equiv|name)=\"([^\"]*)\"[^>]" . "+content=\"([^\"]*)\"[^>]*>/i"
                ,$crawler->html()
                , $data
            );

            if (
                preg_match('/<title>(.+)<\/title>/'
                    , $crawler->html(),$matches) && isset($matches[1])
            )
                $title = $matches[1];
            else
                $title = "Not Found";

            $metadata = [];

            foreach ($data[2] as $key => $value){
                $metadata[$value] = $data[3][$key];
            }

            if(array_key_exists('keywords', $metadata)){
                $result[] = ['keywords' => $metadata['keywords']];
            } else{
                $result[] = ['keywords' => 'Not Found'];
            }

            if(array_key_exists('description', $metadata)){
                $result[] = ['description' => $metadata['description']];
            } else{
                $result[] = ['description' => 'Not Found'];
            }

            $result[] = ['title' => $title];
            $result[] = ['promoted' => true];
        } else{
            $result[] = ['keywords' => 'Not Found'];
            $result[] = ['description' => 'Not Found'];
            $result[] = ['title' => 'Not Found'];
            $result[] = ['promoted' => false];
        }

        $result[] = ['url' => $link];
        $result[] = ['ranking' => $key * 5];


        return json_encode($result);
    }

    public function setEngine($engine){
        $this->engine = $engine;
    }

    public function search($keywords){
        $this->keywords = $keywords;

        $results = new ArrayObject();
        $iterator = $results->getIterator();

        $links = $this->refineSearch($this->basicSearch($keywords));

        foreach ($links as $key => $link){
            $result = $this->getMetaData($key, $link);
            $results->append($result);
        }

//        dd($results);
        return $iterator;
    }
}