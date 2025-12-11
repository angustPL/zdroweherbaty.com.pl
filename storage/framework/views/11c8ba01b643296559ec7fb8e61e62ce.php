<?php $__env->startSection('content'); ?>
    <!-- Greeting -->
    <p style="margin: 0 0 20px; color: #333; font-size: 16px;">
        <?php echo e(__('Good day')); ?> <?php echo e($order->customer_first_name); ?> <?php echo e($order->customer_last_name); ?>,
    </p>

    <p style="margin: 0 0 30px; color: #333; font-size: 16px;">
        <?php echo e(__('Thank you for placing an order in our store. Below are the details of your order.')); ?>

    </p>

    <!-- Order Details -->
    <div class="panel">
        <h3><?php echo e(__('Szczegóły zamówienia')); ?></h3>
        <p><strong><?php echo e(__('Order date')); ?>:</strong> <?php echo e($order->created_at->format('d.m.Y H:i')); ?></p>
        <p><strong>Aktualne dane online:</strong> <a href="<?php echo e(config('app.url')); ?>/zamowienie/<?php echo e($order->ext_order_id); ?>"
                style="color: #026941;">Sprawdź status zamówienia</a></p>
    </div>

    <!-- Customer Data -->
    <div class="panel">
        <h3><?php echo e(__('Customer data')); ?></h3>
        <p><strong>Imię i nazwisko:</strong> <?php echo e($order->customer_first_name); ?> <?php echo e($order->customer_last_name); ?></p>
        <p><strong><?php echo e(__('Address')); ?>:</strong> <?php echo e($order->delivery_street); ?>

            <?php echo e($order->delivery_street_number); ?><?php echo e($order->delivery_apartment ? '/' . $order->delivery_apartment : ''); ?>

        </p>
        <p><strong>Kod Poczta:</strong> <?php echo e($order->delivery_postal_code); ?> <?php echo e($order->delivery_city); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->customer_phone): ?>
            <p><strong><?php echo e(__('Phone')); ?>:</strong> <?php echo e($order->customer_phone); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <p><strong>Adres email:</strong> <?php echo e($order->customer_email); ?></p>
    </div>

    <!-- Payment Information -->
    <div class="panel">
        <h3>Informacje o płatności</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($order->payment)): ?>
            <p><strong>Metoda płatności:</strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payment->isPayu()): ?>
                    PayU - <?php echo e($order->payment->payu_option ?? 'płatność online'); ?>

                <?php elseif($order->payment->isCash()): ?>
                    Płatność przy odbiorze
                <?php else: ?>
                    <?php echo e($order->payment->payment_method ?? 'Brak danych'); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
            <p><strong>Status płatności:</strong> <?php echo e($order->payment->status->label() ?? 'Nieopłacone'); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
                $order->payment->isPayu() &&
                    ($order->payment->status === \App\Enums\PaymentStatus::PENDING ||
                        $order->payment->status === \App\Enums\PaymentStatus::WAITING_FOR_CONFIRMATION)): ?>
                <p style="margin-top: 15px; color: #666;">
                    <strong>Link do płatności:</strong>
                    <a href="<?php echo e(config('app.url')); ?>/payment/payu/<?php echo e($order->ext_order_id); ?>"
                        style="color: #026941;">Dokończ płatność online</a>
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>
            <p><strong>Metoda płatności:</strong> Brak danych</p>
            <p><strong>Status płatności:</strong> Nieokreślony</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->parcel_locker_name): ?>
        <!-- Parcel Locker -->
        <div class="panel">
            <h3><?php echo e(__('Parcel locker')); ?></h3>
            <p><strong><?php echo e(__('Name')); ?>:</strong> <?php echo e($order->parcel_locker_name); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->parcel_locker_address): ?>
                <p><strong><?php echo e(__('Address')); ?>:</strong> <?php echo e($order->parcel_locker_address); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->invoice_nip || $order->invoice_company_name || $order->invoice_address): ?>
        <!-- Invoice Data -->
        <div class="panel">
            <h3><?php echo e(__('Invoice data')); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->invoice_company_name): ?>
                <p><strong><?php echo e(__('Company name')); ?>:</strong> <?php echo e($order->invoice_company_name); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->invoice_nip): ?>
                <p><strong><?php echo e(__('NIP')); ?>:</strong> <?php echo e($order->invoice_nip); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->invoice_address): ?>
                <p><strong><?php echo e(__('Address')); ?>:</strong> <?php echo e($order->invoice_address); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Ordered Items -->
    <div class="panel">
        <h3><?php echo e(__('Ordered items')); ?></h3>
        <table class="email-table">
            <thead>
                <tr>
                    <th><?php echo e(__('No.')); ?></th>
                    <th><?php echo e(__('Product name')); ?></th>
                    <th class="text-right"><?php echo e(__('Price')); ?></th>
                    <th class="text-right"><?php echo e(__('Quantity')); ?></th>
                    <th class="text-right"><?php echo e(__('Sum')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $total = 0;
                    $counter = 1;
                    // Debug - usuń po sprawdzeniu
                    if (request()->has('debug')) {
                        dd($order->items);
                    }
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($counter); ?>.</td>
                        <td><?php echo e($item['name']); ?></td>
                        <td class="text-right"><?php echo e(number_format($item['price'], 2, ',', ' ')); ?> zł</td>
                        <td class="text-right"><?php echo e($item['quantity']); ?></td>
                        <td class="text-right"><strong><?php echo e(number_format($item['price'] * $item['quantity'], 2, ',', ' ')); ?>

                                zł</strong></td>
                    </tr>
                    <?php
                        $total += $item['price'] * $item['quantity'];
                        $counter++;
                    ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr>
                    <td colspan="4" class="text-right"><?php echo e(__('Wartość produktów')); ?>:</td>
                    <td class="text-right"><?php echo e(number_format($total, 2, ',', ' ')); ?> zł</td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->discount_amount > 0): ?>
                    <tr>
                        <td colspan="4" class="text-right" style="color: #28a745;">
                            Zniżka <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->promotion_code): ?>
                                (<?php echo e($order->promotion_code); ?>)
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>:
                        </td>
                        <td class="text-right" style="color: #28a745;">
                            -<?php echo e(number_format($order->discount_amount, 2, ',', ' ')); ?> zł
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr>
                    <td colspan="4" class="text-right"><?php echo e(__('Delivery')); ?> (<?php echo e($order->delivery_name); ?>):</td>
                    <td class="text-right"><?php echo e(number_format($order->delivery_cost, 2, ',', ' ')); ?> zł</td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-right" style="font-size: 1.2em;"><?php echo e(__('Total to pay')); ?>:</td>
                    <td class="text-right" style="font-size: 1.2em;">
                        <?php echo e(number_format($order->total ?? $total, 2, ',', ' ')); ?> zł</td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->notes): ?>
        <!-- Notes -->
        <div class="panel">
            <h3><?php echo e(__('Notes')); ?>:</h3>
            <p style="white-space: pre-wrap;"><?php echo e($order->notes); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Thanks -->
    <p style="margin: 20px 0; color: #333; font-size: 16px;">
        Dziękujemy za zakupy w naszym sklepie!<br>
        W razie pytań prosimy o <a href="mailto:<?php echo e(config('mail.from.address')); ?>?subject=Pytanie dotyczy zamówienia"
            style="color: #026941;">kontakt</a>.
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.app', [
    'title' => __('Order confirmation'),
    'headerUrl' => config('app.url'),
    'logo' => $logo ?? null,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views/emails/order-confirmation-custom.blade.php ENDPATH**/ ?>