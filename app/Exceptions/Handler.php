<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
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
     * Códigos HTTP que ya tienen una vista de alerta propia en resources/views/errors.
     *
     * @var array<int, int>
     */
    private const STATUS_CON_VISTA_PROPIA = [401, 403, 404, 419, 429, 503];

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
     * Reemplaza la página de depuración de Laravel (Ignition) y las páginas
     * de error por defecto por una única alerta con el mensaje del error y
     * una sugerencia de solución, en vez de exponer la traza completa.
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $e instanceof ValidationException || $e instanceof AuthenticationException) {
            return parent::render($request, $e);
        }

        $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

        if (in_array($status, self::STATUS_CON_VISTA_PROPIA, true)) {
            return response()->view("errors.$status", [], $status);
        }

        return response()->view('errors.exception', [
            'titulo' => $this->tituloError($e, $status),
            'mensaje' => $e->getMessage() ?: 'Ocurrió un error inesperado.',
            'solucion' => $this->sugerenciaSolucion($e),
            'debug' => (bool) config('app.debug'),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
        ], $status);
    }

    private function tituloError(Throwable $e, int $status): string
    {
        return match (true) {
            $e instanceof QueryException => 'Error de base de datos',
            $e instanceof ModelNotFoundException => 'Registro no encontrado',
            $e instanceof TokenMismatchException => 'Sesión expirada',
            default => $status === 500 ? 'Error del servidor' : "Error {$status}",
        };
    }

    private function sugerenciaSolucion(Throwable $e): string
    {
        return match (true) {
            $e instanceof QueryException => 'Revisa la consulta SQL generada, los nombres de tabla/columna involucrados y la conexión a la base de datos.',
            $e instanceof ModelNotFoundException => 'El registro solicitado no existe o fue eliminado. Verifica el identificador usado.',
            $e instanceof TokenMismatchException => 'La sesión expiró o el token CSRF no es válido. Recarga la página e intenta nuevamente.',
            default => 'Revisa el mensaje, el archivo y la línea indicados para ubicar la causa. Si el problema persiste, contacta al administrador del sistema.',
        };
    }
}
