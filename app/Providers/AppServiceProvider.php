<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Transaction;
use App\Models\TopupTransaction;
use App\Models\Layanan;
use App\Models\Setting;
use App\Observers\TransactionObserver;
use App\Observers\TopupTransactionObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        TopupTransaction::observe(TopupTransactionObserver::class);

        // Map route names → seo slugs stored in settings table
        $seoSlugMap = [
            'landing'    => 'home',
            'home'       => 'home',
            'about'      => 'about',
            'pricing'    => 'pricing',
            'blog.index' => 'blog',
            'blog.category' => 'blog',
            'contact'    => 'contact',
            'login'      => 'login',
            'register'   => 'register',
            'password.request' => 'login',
            'password.otp.form' => 'login',
            'password.reset' => 'login',
        ];

        // SEO View Composer — only for the main layout
        View::composer(['layouts.app', 'layouts.auth'], function ($view) use ($seoSlugMap) {
            $routeName = Route::currentRouteName() ?? '';
            $slug      = $seoSlugMap[$routeName] ?? '';

            $existingData = $view->getData();
            $existingTitle = $existingData['seoTitle'] ?? null;
            $existingDesc = $existingData['seoDesc'] ?? null;
            $existingKeywords = $existingData['seoKeywords'] ?? null;

            $seoTitle    = null;
            $seoDesc     = null;
            $seoKeywords = null;

            if ($slug) {
                try {
                    $keys = ["seo_title_{$slug}", "seo_desc_{$slug}", "seo_keywords_{$slug}"];
                    $rows = Setting::whereIn('key', $keys)->get()->keyBy('key');

                    $seoTitle    = $rows["seo_title_{$slug}"]->value    ?? null;
                    $seoDesc     = $rows["seo_desc_{$slug}"]->value     ?? null;
                    $seoKeywords = $rows["seo_keywords_{$slug}"]->value ?? null;
                } catch (\Throwable $e) {
                    // Tetap render view walau DB belum siap/tidak tersedia.
                }
            }

            $seoTitle = $existingTitle ?: $seoTitle;
            $seoDesc = $existingDesc ?: $seoDesc;
            $seoKeywords = $existingKeywords ?: $seoKeywords;

            $view->with(compact('seoTitle', 'seoDesc', 'seoKeywords'));
        });

        // General View Composer — shared data for all views
        View::composer('*', function ($view) {
            $jenis = collect();
            $tambahan = collect();

            try {
                $jenis = Layanan::where('category', 'jenis')->get();
                $tambahan = Layanan::where('category', 'tambahan')->get();
            } catch (\Throwable $e) {
                // Tetap render view walau DB belum siap/tidak tersedia.
            }

            $cartItemCount = 0;
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $cartItemCount = $user->transactions()->where('status', Transaction::STATUS_PENDING)->count();
            }

            $view->with(compact('jenis', 'tambahan', 'cartItemCount'));
        });
    }
}
