<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    protected $fillable = [
        'id',
        'email',
        'unsubscribe_token',
        'unsubscribed_at',
        'ip_address',
    ];

    protected $casts = [
        'id' => 'string',
        'unsubscribed_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid();
            $model->unsubscribe_token = hash('sha256', $model->id . $model->email . config('app.key'));
        });
    }

    public function isSubscribed()
    {
        return is_null($this->unsubscribed_at);
    }

    public function unsubscribe()
    {
        $this->unsubscribed_at = now();
        $this->save();
    }

    public function getUnsubscribeUrlAttribute()
    {
        return route('newsletter.unsubscribe', [$this->id, $this->unsubscribe_token]);
    }
}
