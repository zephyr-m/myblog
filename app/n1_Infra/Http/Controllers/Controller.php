<?php

namespace App\n1_Infra\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as LaravelController;

abstract class Controller extends LaravelController
{
    use AuthorizesRequests;
}
