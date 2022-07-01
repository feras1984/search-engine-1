<?php
namespace HttpClient;

interface HttpCustomClientContract
{
    public function setParameters($method, $link, $selector = '');

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function getClient();
}