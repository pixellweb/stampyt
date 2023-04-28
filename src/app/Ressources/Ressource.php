<?php

namespace Citadelle\Stampyt\app\Ressources;


use Citadelle\Stampyt\app\Api;

class Ressource
{
    /**
     * @var Api
     */
    public $api;


    /**
     * Ressource constructor.
     */
    public function __construct()
    {
        $this->api = new Api();
    }

}
