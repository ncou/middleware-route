<?php

namespace Phapi\Tests;

class Page {

    public function __construct($request, $response, $container)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
    }

    public function get()
    {
        return [
            'id' => 123456
        ];
    }

    public function getResponse()
    {
        return $this->response;
    }

}