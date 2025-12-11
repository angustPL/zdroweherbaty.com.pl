<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?php echo e($title ?? config('app.name')); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        /* Reset styles */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        /* Main styles */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            background-color: #f6f6f6;
        }

        .content {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            padding: 30px 30px 20px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }

        .header img {
            max-width: 160px;
            height: auto;
        }

        .header h1 {
            margin: 20px 0 0;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }

        .body {
            background-color: #ffffff;
            padding: 0;
            border-radius: 8px;
        }

        .inner-body {
            width: 100%;
            max-width: 570px;
            margin: 0 auto;
            padding: 30px;
        }

        .panel {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .panel h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #026941;
            font-size: 16px;
            font-weight: bold;
        }

        .panel p {
            margin: 5px 0;
            color: #333;
        }

        .panel strong {
            color: #000;
        }

        .footer {
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }

        /* Table styles */
        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .email-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
            color: #495057;
        }

        .email-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .email-table tr:last-child td {
            border-bottom: none;
        }

        .email-table .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .email-table .total-row td {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #026941;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
                padding: 20px !important;
            }

            .footer {
                width: 100% !important;
            }

            .email-table th,
            .email-table td {
                padding: 8px !important;
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body>

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" cellpadding="0" cellspacing="0" role="presentation"
                    style="margin: 0 auto; min-width: 600px;">

                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headerUrl ?? false): ?>
                                <a href="<?php echo e($headerUrl); ?>" style="display: inline-block;">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo ?? false): ?>
                                <img src="<?php echo e($logo); ?>" alt="<?php echo e(config('app.name')); ?> Logo"
                                    style="max-width: 160px; height: auto;" />
                            <?php else: ?>
                                <img src="<?php echo e(config('app.url')); ?>/img/bifix-logo.png"
                                    alt="<?php echo e(config('app.name')); ?> Logo" style="max-width: 160px; height: auto;" />
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headerUrl ?? false): ?>
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($title ?? false): ?>
                                <h1><?php echo e($title); ?></h1>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="body">
                            <table class="inner-body" align="center" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <tr>
                                    <td>
                                        <?php echo $__env->yieldContent('content'); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <?php echo e($footer ?? '© ' . date('Y') . ' ' . config('app.name') . '. ' . __('All rights reserved.')); ?>

                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
<?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views/emails/layouts/app.blade.php ENDPATH**/ ?>