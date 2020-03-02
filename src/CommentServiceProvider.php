<?php

namespace MetaDevStudio\LaravelComment;

use Illuminate\Support\ServiceProvider;

class CommentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configFile = __DIR__ . '/../config/comment.php';

        $this->publishes([
            $configFile => config_path('comment.php'),
        ], 'config');

        if (!class_exists('CreateCommentsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_comments_table.php' =>
                    database_path("migrations/{$timestamp}_create_comments_table.php")
            ], 'migrations');
        }
    }
}
