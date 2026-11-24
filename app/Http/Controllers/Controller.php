<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Opérateur de recherche insensible à la casse : sous PostgreSQL, LIKE distingue
     * majuscules/minuscules ("gallet" ne trouverait pas "GALLET"), on utilise donc ILIKE.
     */
    protected static function likeOperator(): string
    {
        return \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
