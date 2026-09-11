<?php

namespace App\n2_System\Widgets\Http\Controllers;

use App\n1_Infra\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WidgetController extends Controller
{
    public function __invoke(): Response
    {
        Gate::authorize('permission', 'widget.view');

        return Inertia::render('system/WidgetLab');
    }
}
