<?php

declare(strict_types=1);

namespace Illuminate\Support {
    if (! function_exists('Illuminate\Support\extension_loaded')) {
        /**
         * Polyfill extension_loaded for Illuminate\Support namespace
         * to allow symfony/polyfill-intl-icu to be used by Number::format
         * when the native ext-intl PHP C extension is not installed.
         */
        function extension_loaded(string $extension): bool
        {
            if ($extension === 'intl') {
                return true;
            }

            return \extension_loaded($extension);
        }
    }
}
