<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;

class ApiController extends AbstractController
{
    public function ping()
    {
        return "pong";
    }
}