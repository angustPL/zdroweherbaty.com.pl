<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;

class NewsletterModal extends Component
{
    public $email = '';
    public $showModal = true;
    public $isSubscribed = false;
    public $errorMessage = '';

    public function mount()
    {
        // Zawsze pokazuj modal do testów
        $this->showModal = true;
    }

    public function closeModal()
    {
        // Ustaw cookie na 1 dzień
        Cookie::queue('newsletter_hide', 'true', 1440);
        $this->showModal = false;
    }

    public function subscribe()
    {
        $this->validate([
            'email' => 'required|email|unique:newsletter_subscriptions,email',
        ]);

        try {
            // Sprawdź czy email już istnieje (ale nie jest wypisany)
            $existing = NewsletterSubscription::where('email', $this->email)
                ->whereNull('unsubscribed_at')
                ->first();

            if ($existing) {
                $this->errorMessage = 'Ten adres email jest już zapisany na newsletter.';
                return;
            }

            // Jeśli istnieje ale był wypisany, reaktywuj
            if ($existing = NewsletterSubscription::where('email', $this->email)->first()) {
                $existing->unsubscribed_at = null;
                $existing->ip_address = Request::ip();
                $existing->save();
                $subscription = $existing;
            } else {
                // Nowy zapis
                $subscription = NewsletterSubscription::create([
                    'email' => $this->email,
                    'ip_address' => Request::ip(),
                ]);
            }

            // Wyślij email potwierdzający
            Mail::to(env('ORDER_EMAIL', 'admin@zdroweherbaty.com.pl'))
                ->send(new \App\Mail\NewsletterSubscriptionMail($subscription));

            // Ustaw cookie na rok
            Cookie::queue('newsletter_subscribed', 'true', 525600); // 1 rok w minutach

            // GTM event
            $this->dispatch('newsletterSignup', ['email' => $this->email]);

            $this->isSubscribed = true;
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd. Spróbuj ponownie.';
        }
    }


    public function render()
    {
        return view('livewire.newsletter-modal');
    }
}
