<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Gérer l'erreur 419 (Token CSRF expiré)
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            // Rediriger vers la page de connexion avec un message
            return redirect()
                ->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        // Filet de sécurité : violation de contrainte de clé étrangère (ex: suppression
        // concurrente d'un enregistrement encore référencé, malgré les vérifications applicatives)
        if ($e instanceof QueryException && in_array($e->getCode(), ['23503', '23000'], true)) {
            return back()->with('error', 'Cette action est impossible car l\'enregistrement est référencé ailleurs dans l\'application.');
        }

        return parent::render($request, $e);
    }
}
