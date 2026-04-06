<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * `AuthorizesRequests` permet $this->authorize(...) dans les contrôleurs
 * et les policies enregistrées (ClientPolicy, RequetesPolicy, …).
 */
abstract class Controller
{
    use AuthorizesRequests;
}
