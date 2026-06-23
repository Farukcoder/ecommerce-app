<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if ?lang or ?locale parameter is present in request
        $locale = $request->query('lang');
        if (! $locale) {
            $locale = $request->query('locale');
        }

        // 2. Otherwise check X-Locale header
        if (! $locale) {
            $locale = $request->header('X-Locale');
        }

        // 3. Otherwise check session (for web requests)
        if (! $locale && $request->hasSession()) {
            $locale = (string) session('locale');
        }

        // 4. Otherwise check Accept-Language header
        if (! $locale) {
            $acceptLanguage = $request->header('Accept-Language');
            if ($acceptLanguage) {
                // Parse first language (e.g. en-US,en;q=0.9,bn;q=0.8 => en)
                $locales = explode(',', $acceptLanguage);
                $firstLocale = explode(';', $locales[0] ?? '');
                $locale = trim(explode('-', $firstLocale[0])[0]);
            }
        }

        // Validate that it's a supported locale
        $supportedLocales = ['en', 'bn'];
        try {
            $setting = SystemSetting::query()->latest('id')->first();
            if ($setting && $setting->available_locales) {
                $supportedLocales = collect($setting->available_locales)->pluck('code')->all();
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet or migration not run
        }

        if ($locale && in_array(strtolower($locale), $supportedLocales, true)) {
            $locale = strtolower($locale);
            App::setLocale($locale);
            if ($request->hasSession()) {
                session(['locale' => $locale]);
            }
        } else {
            // Default locale
            try {
                $setting = SystemSetting::query()->latest('id')->first();
                $default = $setting ? ($setting->default_locale ?? 'en') : 'en';
                App::setLocale($default);
            } catch (\Exception $e) {
                App::setLocale('en');
            }
        }

        return $next($request);
    }
}
