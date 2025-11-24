<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\EnovaXmlService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateOrderXml
{
    public function __construct(
        private Container $container
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            $xmlService = $this->container->make(EnovaXmlService::class);
            $xmlDirectory = Config::get('enova.orders.xml_directory', Storage::path('app/enova/orders'));

            // Zapisuj XML do pliku
            $localPath = $xmlService->saveToFile($event->order, $xmlDirectory);

            // Wysyłaj XML do Enova (FTP lub kopia)
            $xmlService->sendXml($event->order, $localPath);
        } catch (\Exception $e) {
            Log::error('Błąd generowania XML dla zamówienia: ' . $e->getMessage(), [
                'order_id' => $event->order->id,
                'ext_order_id' => $event->order->ext_order_id,
                'exception' => $e,
            ]);
        }
    }
}
