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
    public $showModal = false;
    public $isSubscribed = false;
    public $errorMessage = '';

    public function mount()
    {
        // Jeden status dla newslettera: null / 'hidden' / 'subscribed'
        $status = Cookie::get('newsletter_status');

        // Pokaż modal tylko gdy nie ma żadnego statusu
        $this->showModal = !$status;
    }

    public function closeModal()
    {
        // Tylko zamknięcie modala – bez zmiany cookies
        $this->showModal = false;
    }

    public function hideForToday()
    {
        // Ustaw status "hidden" na 1 dzień po świadomym kliknięciu "Nie dzisiaj"
        Cookie::queue('newsletter_status', 'hidden', 60 * 24); // 1 dzień

        // Zamknij modal
        $this->closeModal();
    }

    public function subscribe()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        try {
            // Szukamy dowolnej subskrypcji dla tego emaila
            $existing = NewsletterSubscription::where('email', $this->email)->first();

            if ($existing) {
                if (is_null($existing->unsubscribed_at)) {
                    // CASE 1: Już aktywny subskrybent – odświeżamy dane
                    $existing->ip_address = Request::ip();
                    $existing->touch(); // aktualizuje updated_at
                    $subscription = $existing;
                } else {
                    // CASE 2: Był wypisany – reaktywujemy
                    $existing->unsubscribed_at = null;
                    $existing->ip_address = Request::ip();
                    $existing->save();
                    $subscription = $existing;
                }
            } else {
                // CASE 3: Nowy subskrybent
                $subscription = NewsletterSubscription::create([
                    'email'      => $this->email,
                    'ip_address' => Request::ip(),
                ]);
            }

            // Wyślij email do admina
            Mail::to(env('ORDER_EMAIL', 'admin@zdroweherbaty.com.pl'))
                ->send(new \App\Mail\NewsletterSubscriptionMail($subscription));

            // Wyślij email potwierdzający do użytkownika
            Mail::to($this->email)
                ->send(new \App\Mail\NewsletterUserWelcomeMail($subscription));

            // Ustaw status "subscribed" na rok
            Cookie::queue('newsletter_status', 'subscribed', 60 * 24 * 365); // 1 rok

            // GTM event
            $this->dispatch('newsletterSignup', ['email' => $this->email]);

            // Widok podziękowania
            $this->isSubscribed = true;
            $this->errorMessage = '';
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd. Spróbuj ponownie.';
        }
    }


    public function render()
    {
        return view('livewire.newsletter-modal');
    }
}
