<?php

declare(strict_types=1);

$business = is_array($invoice['business'] ?? null) ? $invoice['business'] : [];
$client = is_array($invoice['client'] ?? null) ? $invoice['client'] : [];
$items = is_array($items ?? null) ? $items : [];
$currency = strtoupper((string) ($invoice['currency'] ?? 'USD'));
$status = (string) ($invoice['display_status'] ?? $invoice['status'] ?? 'draft');
$statusLabels = [
    'draft' => 'Draft',
    'sent' => 'Sent',
    'paid' => 'Paid',
    'overdue' => 'Overdue',
    'cancelled' => 'Void',
];

$formatDate = static function (mixed $value): string {
    $date = is_string($value) ? strtotime($value) : false;

    return $date === false ? '' : date('M j, Y', $date);
};

$formatMoney = static function (mixed $cents) use ($currency): string {
    return $currency . ' ' . number_format(((int) $cents) / 100, 2);
};

$formatQuantity = static function (mixed $hundredths, mixed $raw = null): string {
    if (is_numeric($hundredths)) {
        $value = ((int) $hundredths) / 100;
    } elseif (is_numeric($raw)) {
        $value = (float) $raw;
    } else {
        $value = 0.0;
    }

    $formatted = number_format($value, 2, '.', '');
    $trimmed = rtrim(rtrim($formatted, '0'), '.');

    return $trimmed === '' ? '0' : $trimmed;
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= e((string) ($invoice['invoice_number'] ?? 'Invoice')) ?></title>
    <style>
        @page {
            margin: 34px 38px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        .header-table,
        .details-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td,
        .details-table td {
            vertical-align: top;
        }

        .brand {
            width: 58%;
            padding-right: 24px;
        }

        .invoice-meta {
            width: 42%;
            text-align: right;
        }

        .logo {
            max-width: 170px;
            max-height: 78px;
            margin-bottom: 12px;
        }

        .business-name {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .invoice-title {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .muted {
            color: #64748b;
        }

        .small {
            font-size: 10px;
        }

        .meta-table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .meta-table td:first-child {
            color: #64748b;
            text-align: left;
        }

        .meta-table td:last-child {
            font-weight: 600;
            text-align: right;
        }

        .status {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 10px;
            background: #e2e8f0;
            color: #334155;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .status-paid,
        .status-sent {
            background: #dcfce7;
            color: #166534;
        }

        .status-overdue {
            background: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .section {
            margin-top: 30px;
        }

        .details-table td {
            width: 50%;
        }

        .details-table td:first-child {
            padding-right: 20px;
        }

        .card {
            min-height: 120px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .card-title {
            margin-bottom: 8px;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .card strong {
            color: #0f172a;
            font-size: 13px;
        }

        .items-table {
            margin-top: 12px;
        }

        .items-table th {
            padding: 9px 8px;
            border-bottom: 1px solid #cbd5e1;
            background: #f1f5f9;
            color: #475569;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table th:first-child {
            border-radius: 6px 0 0 6px;
            text-align: center;
            width: 34px;
        }

        .items-table th:last-child {
            border-radius: 0 6px 6px 0;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .items-table td:first-child {
            color: #94a3b8;
            text-align: center;
        }

        .items-table .description {
            font-weight: 600;
        }

        .items-table .number {
            text-align: right;
            white-space: nowrap;
        }

        .totals-wrap {
            width: 100%;
            margin-top: 18px;
        }

        .totals-table {
            width: 290px;
            margin-left: auto;
        }

        .totals-table td {
            padding: 4px 0;
        }

        .totals-table td:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .totals-table .grand-total td {
            padding-top: 10px;
            border-top: 2px solid #0f172a;
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
        }

        .note-box {
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
        }

        .footer {
            margin-top: 34px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 10px;
            text-align: center;
        }

        .pre-line {
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="brand">
                <?php if ($logoDataUri !== ''): ?>
                    <img src="<?= e($logoDataUri) ?>" alt="Business logo" class="logo">
                <?php endif; ?>

                <div class="business-name"><?= e((string) ($business['name'] ?? 'Invoice Generator')) ?></div>

                <?php if (!empty($business['address'])): ?>
                    <div class="muted pre-line small"><?= e((string) $business['address']) ?></div>
                <?php endif; ?>

                <div class="muted small">
                    <?php $businessContact = array_filter([
                        (string) ($business['email'] ?? ''),
                        (string) ($business['phone'] ?? ''),
                        (string) ($business['website'] ?? ''),
                    ], static fn (string $value): bool => $value !== ''); ?>
                    <?= e(implode('  |  ', $businessContact)) ?>
                </div>

                <?php if (!empty($business['tax_id'])): ?>
                    <div class="muted small">Tax ID: <?= e((string) $business['tax_id']) ?></div>
                <?php endif; ?>
            </td>
            <td class="invoice-meta">
                <h1 class="invoice-title">INVOICE</h1>

                <table class="meta-table">
                    <tr>
                        <td>Invoice number</td>
                        <td><?= e((string) ($invoice['invoice_number'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <td>Issue date</td>
                        <td><?= e($formatDate($invoice['issue_date'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td>Due date</td>
                        <td><?= e($formatDate($invoice['due_date'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            <span class="status status-<?= e($status) ?>">
                                <?= e($statusLabels[$status] ?? ucfirst($status)) ?>
                            </span>
                        </td>
                    </tr>
                    <?php if (!empty($invoice['paid_at'])): ?>
                        <tr>
                            <td>Paid on</td>
                            <td><?= e($formatDate($invoice['paid_at'])) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>

    <div class="section">
        <table class="details-table">
            <tr>
                <td>
                    <div class="card">
                        <div class="card-title">Bill To</div>
                        <strong><?= e((string) ($client['name'] ?? 'Client')) ?></strong>

                        <?php if (!empty($client['company'])): ?>
                            <div><?= e((string) $client['company']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($client['address'])): ?>
                            <div class="muted pre-line"><?= e((string) $client['address']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($client['email'])): ?>
                            <div class="muted"><?= e((string) $client['email']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($client['tax_id'])): ?>
                            <div class="muted small">Tax ID: <?= e((string) $client['tax_id']) ?></div>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="card-title">Payment Details</div>

                        <?php if (!empty($business['bank_details'])): ?>
                            <div class="pre-line"><?= e((string) $business['bank_details']) ?></div>
                        <?php else: ?>
                            <div class="muted">Bank details have not been configured.</div>
                        <?php endif; ?>

                        <?php if (!empty($business['payment_terms'])): ?>
                            <div class="muted small" style="margin-top: 10px;">
                                Terms: <?= e((string) $business['payment_terms']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="number">Qty</th>
                    <th class="number">Unit price</th>
                    <th class="number">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items === []): ?>
                    <tr>
                        <td colspan="5" class="muted">No line items.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= e((string) ($index + 1)) ?></td>
                            <td class="description pre-line"><?= e((string) ($item['description'] ?? '')) ?></td>
                            <td class="number"><?= e($formatQuantity($item['quantity_hundredths'] ?? null, $item['quantity'] ?? null)) ?></td>
                            <td class="number"><?= e($formatMoney($item['unit_price_cents'] ?? 0)) ?></td>
                            <td class="number"><?= e($formatMoney($item['total_cents'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals-table">
                <tr>
                    <td class="muted">Subtotal</td>
                    <td><?= e($formatMoney($invoice['subtotal_cents'] ?? 0)) ?></td>
                </tr>
                <?php if ((int) ($invoice['discount_cents'] ?? 0) > 0): ?>
                    <tr>
                        <td class="muted">Discount</td>
                        <td>-<?= e($formatMoney($invoice['discount_cents'] ?? 0)) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ((int) ($invoice['tax_cents'] ?? 0) > 0): ?>
                    <tr>
                        <td class="muted">Tax</td>
                        <td><?= e($formatMoney($invoice['tax_cents'] ?? 0)) ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="grand-total">
                    <td>Total</td>
                    <td><?= e($formatMoney($invoice['total_cents'] ?? 0)) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if (!empty($invoice['notes'])): ?>
        <div class="section note-box">
            <div class="card-title">Notes</div>
            <div class="pre-line"><?= e((string) $invoice['notes']) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($business['footer_note'])): ?>
        <div class="footer pre-line"><?= e((string) $business['footer_note']) ?></div>
    <?php endif; ?>
</body>
</html>
