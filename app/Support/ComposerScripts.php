<?php

namespace App\Support;

use Composer\Script\Event;
use Illuminate\Contracts\Console\Kernel;
use Throwable;

class ComposerScripts
{
    public static function safePackageDiscover(Event $event): void
    {
        if (filter_var((string) getenv('SKIP_PACKAGE_DISCOVER'), FILTER_VALIDATE_BOOL)) {
            $event->getIO()->write('<info>Skipping package discovery (SKIP_PACKAGE_DISCOVER=true).</info>');

            return;
        }

        try {
            $app = require __DIR__ . '/../../bootstrap/app.php';
            $kernel = $app->make(Kernel::class);
            $status = $kernel->call('package:discover', ['--ansi' => true]);

            if ($status !== 0) {
                $event->getIO()->writeError('<warning>package:discover returned non-zero status and was skipped for shared-hosting compatibility.</warning>');
            }
        } catch (Throwable $e) {
            $event->getIO()->writeError('<warning>Skipping package:discover due to environment limitation: ' . $e->getMessage() . '</warning>');
        }
    }
}
