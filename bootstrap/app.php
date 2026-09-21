<?php

require_once __DIR__.'/../app/Support/polyfill.php';

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (QueryException $e, Request $request) {
            // Intercept foreign key constraint violations (MySQL 1451 / SQLSTATE 23000)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1451')) {
                Log::warning('Foreign key integrity constraint intercepted: '.$e->getMessage());

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'This record cannot be deleted or modified because it is referenced by existing transactions or records.',
                        'error' => 'INTEGRITY_CONSTRAINT_VIOLATION',
                    ], 422);
                }

                return back()->with('error', 'This record cannot be deleted because it is referenced by existing transactions or records.');
            }
        });
    })->create();
