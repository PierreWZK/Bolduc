<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject('Ton nouveau mot de passe Bolduc')
            ->greeting('Bonjour '.$user->name.',')
            ->line('Tu as demandé à changer ton mot de passe. Le lien est valable une heure.')
            ->action('Choisir un mot de passe', route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->line('Ce n\'est pas toi ? Ignore ce message, rien ne change.')
            ->salutation('À bientôt sur Bolduc'));
    }
}
