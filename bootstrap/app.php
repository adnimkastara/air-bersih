<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'api.token' => \App\Http\Middleware\ApiTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $jsonError = function (string $message, int $status, array $errors = []) {
            $payload = [
                'success' => false,
                'message' => $message,
            ];
            if ($errors !== []) {
                $payload['errors'] = $errors;
            }

            return response()->json($payload, $status);
        };

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $message = trim($exception->getMessage()) !== ''
                ? $exception->getMessage()
                : 'Token tidak valid atau sudah logout';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($jsonError) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return $jsonError('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, $exception->errors());
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($jsonError) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return $jsonError('Data tidak ditemukan', Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) use ($jsonError) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return $jsonError('Method tidak didukung untuk endpoint ini', Response::HTTP_METHOD_NOT_ALLOWED);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) use ($jsonError) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if (config('app.debug')) {
                return null;
            }

            return $jsonError('Terjadi kesalahan server', Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
