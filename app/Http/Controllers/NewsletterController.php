<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscriptions,email',
        ]);

        try {
            $subscription = NewsletterSubscription::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
            ]);

            // Wyślij email
            Mail::to(env('ORDER_EMAIL', 'admin@zdroweherbaty.com.pl'))
                ->send(new NewsletterSubscriptionMail($subscription));

            // Ustaw cookie na rok
            Cookie::queue('newsletter_subscribed', 'true', 525600);

            return redirect()->back()->with('success', 'Dziękujemy za zapis na newsletter!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Wystąpił błąd. Spróbuj ponownie.');
        }
    }

    public function unsubscribe($id, $token)
    {
        $subscription = NewsletterSubscription::findOrFail($id);

        // Walidacja tokenu
        $expectedToken = hash('sha256', $subscription->id . $subscription->email . config('app.key'));

        if ($expectedToken !== $token) {
            abort(403, 'Nieprawidłowy token wypisania.');
        }

        // Sprawdź czy już nie jest wypisany
        if ($subscription->unsubscribed_at) {
            return view('newsletter.unsubscribed', ['alreadyUnsubscribed' => true]);
        }

        // Wypisz z newslettera
        $subscription->unsubscribe();

        return view('newsletter.unsubscribed', ['alreadyUnsubscribed' => false]);
    }
}
