<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\ClientRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\SettingsRepository;
use App\Services\InvoiceDocument;
use App\Services\Money;
use DateTimeImmutable;
use InvalidArgumentException;

final class InvoiceController
{
    public function __construct(
        private readonly Config $config,
        private readonly InvoiceRepository $invoices,
        private readonly ClientRepository $clients,
        private readonly SettingsRepository $settings,
        private readonly InvoiceDocument $documents,
        private readonly Money $money,
    ) {
    }

    public function index(): void
    {
        render($this->config->basePath(), 'invoices/index', [
            'title' => 'Invoices',
            'active' => 'invoices',
            'invoices' => $this->invoices->all(),
        ]);
    }

    public function show(string $id): void
    {
        if (preg_match('/^\d+$/', $id) !== 1) {
            $this->notFound();
        }

        $invoiceId = (int) $id;

        if ($invoiceId <= 0) {
            $this->notFound();
        }

        $invoice = $this->documents->find($invoiceId);

        if ($invoice === null) {
            $this->notFound();
        }

        $items = is_array($invoice['items'] ?? null) ? $invoice['items'] : [];

        render($this->config->basePath(), 'invoices/show', [
            'title' => (string) ($invoice['invoice_number'] ?? 'Invoice'),
            'active' => 'invoices',
            'invoice' => $invoice,
            'items' => $items,
        ]);
    }

    public function create(): void
    {
        $clients = $this->clients->all();
        $profile = $this->settings->get();
        $selectedClientId = (int) ($_GET['client_id'] ?? 0);

        if (!$this->clientExists($clients, $selectedClientId)) {
            $selectedClientId = 0;
        }

        render($this->config->basePath(), 'invoices/create', [
            'title' => 'New Invoice',
            'active' => 'invoices',
            'clients' => $clients,
            'profile' => $profile,
            'form' => $this->defaultForm($profile, $selectedClientId),
            'items' => [$this->blankItem()],
            'errors' => [],
            'itemErrors' => [],
        ]);
    }

    public function store(): void
    {
        $clients = $this->clients->all();
        $profile = $this->settings->get();

        $form = [
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'issue_date' => trim((string) ($_POST['issue_date'] ?? '')),
            'due_date' => trim((string) ($_POST['due_date'] ?? '')),
            'currency' => strtoupper(trim((string) ($_POST['currency'] ?? ''))),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'tax_rate' => trim((string) ($_POST['tax_rate'] ?? '')),
            'discount' => trim((string) ($_POST['discount'] ?? '')),
        ];

        $errors = [];
        $itemErrors = [];
        $client = null;

        if ($form['client_id'] <= 0) {
            $errors['client_id'] = 'Select a client.';
        } else {
            $client = $this->clients->find($form['client_id']);

            if ($client === null) {
                $errors['client_id'] = 'Select a valid client.';
            }
        }

        $issueDate = $this->parseDate($form['issue_date']);

        if ($issueDate === null) {
            $errors['issue_date'] = 'Enter a valid issue date.';
        } elseif ((int) $issueDate->format('Y') < 1900) {
            $errors['issue_date'] = 'Issue date must be in 1900 or later.';
        }

        $dueDate = $this->parseDate($form['due_date']);

        if ($dueDate === null) {
            $errors['due_date'] = 'Enter a valid due date.';
        } elseif ($issueDate !== null && $dueDate < $issueDate) {
            $errors['due_date'] = 'Due date cannot be before the issue date.';
        }

        if (preg_match('/^[A-Z]{3}$/', $form['currency']) !== 1) {
            $errors['currency'] = 'Enter a 3-letter currency code such as USD.';
        }

        $taxRateBasisPoints = $this->parseScaledDecimal($form['tax_rate']);

        if ($taxRateBasisPoints === null) {
            $errors['tax_rate'] = 'Enter a valid tax rate.';
        } elseif ($taxRateBasisPoints > 10000) {
            $errors['tax_rate'] = 'Tax rate must be between 0 and 100.';
        }

        $discountBasisPoints = $this->parseScaledDecimal($form['discount']);

        if ($discountBasisPoints === null) {
            $errors['discount'] = 'Enter a valid discount.';
        } elseif ($discountBasisPoints > 10000) {
            $errors['discount'] = 'Discount must be between 0 and 100.';
        }

        [$items, $normalizedItems] = $this->collectItems($itemErrors);

        if ($items === []) {
            $items = [$this->blankItem()];
        }

        $subtotalCents = null;
        $discountCents = null;
        $taxCents = null;
        $totalCents = null;

        if ($errors === [] && $itemErrors === []) {
            try {
                $subtotalCents = 0;

                foreach ($normalizedItems as $item) {
                    if ($subtotalCents > PHP_INT_MAX - $item['total_cents']) {
                        throw new InvalidArgumentException('Invoice subtotal exceeds the supported range.');
                    }

                    $subtotalCents += $item['total_cents'];
                }

                $discountCents = $this->money->discountAmount($subtotalCents, $discountBasisPoints);
                $discountedSubtotal = $this->money->discountedSubtotal($subtotalCents, $discountBasisPoints);
                $taxCents = $this->money->taxAmount($discountedSubtotal, $taxRateBasisPoints);
                $totalCents = $this->money->total($discountedSubtotal, $taxCents);
            } catch (InvalidArgumentException $exception) {
                $errors['general'] = 'The invoice totals are too large. Please reduce the line item amounts.';
            }
        }

        if ($errors !== [] || $itemErrors !== []) {
            http_response_code(422);

            render($this->config->basePath(), 'invoices/create', [
                'title' => 'New Invoice',
                'active' => 'invoices',
                'clients' => $clients,
                'profile' => $profile,
                'form' => $form,
                'items' => $items,
                'errors' => $errors,
                'itemErrors' => $itemErrors,
            ]);

            return;
        }

        $result = $this->invoices->save([
            'client_id' => $form['client_id'],
            'invoice_prefix' => $this->invoicePrefix($profile),
            'issue_date' => $form['issue_date'],
            'due_date' => $form['due_date'],
            'currency' => $form['currency'],
            'notes' => $form['notes'] === '' ? null : $form['notes'],
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountCents,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
            'client_snapshot' => $client,
            'seller_snapshot' => $profile,
            'items' => $normalizedItems,
        ]);

        $_SESSION['flash_success'] = 'Draft invoice ' . $result['invoice_number'] . ' created.';

        redirect('/invoices');
    }

    private function notFound(): never
    {
        http_response_code(404);

        render($this->config->basePath(), 'errors/404', [
            'title' => 'Invoice Not Found',
            'active' => 'invoices',
            'message' => 'The requested invoice could not be found.',
        ]);

        exit;
    }

    /**
     * @param array<string, string> $profile
     * @return array<string, string|int>
     */
    private function defaultForm(array $profile, int $clientId): array
    {
        $paymentTerms = (string) ($profile['payment_terms'] ?? '');
        $days = 30;

        if (preg_match('/(\d{1,3})/', $paymentTerms, $matches) === 1) {
            $days = max(0, min(365, (int) $matches[1]));
        }

        $issueDate = date('Y-m-d');

        return [
            'client_id' => $clientId,
            'issue_date' => $issueDate,
            'due_date' => date('Y-m-d', strtotime($issueDate . ' +' . $days . ' days')),
            'currency' => strtoupper((string) ($profile['currency'] ?? 'USD')),
            'notes' => '',
            'tax_rate' => (string) ($profile['default_tax_rate'] ?? '0.00'),
            'discount' => '0.00',
        ];
    }

    /**
     * @return array{description: string, quantity: string, unit_price: string}
     */
    private function blankItem(): array
    {
        return [
            'description' => '',
            'quantity' => '1',
            'unit_price' => '',
        ];
    }

    /**
     * @param array<int|string, mixed> $itemErrors
     * @return array{0: array<int, array<string, string>>, 1: array<int, array{
     *     description: string,
     *     quantity_hundredths: int,
     *     unit_price_cents: int,
     *     total_cents: int
     * }>}
     */
    private function collectItems(array &$itemErrors): array
    {
        $descriptions = $_POST['item_description'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $unitPrices = $_POST['item_unit_price'] ?? [];

        $descriptions = is_array($descriptions) ? array_values($descriptions) : [];
        $quantities = is_array($quantities) ? array_values($quantities) : [];
        $unitPrices = is_array($unitPrices) ? array_values($unitPrices) : [];

        $rowCount = max(count($descriptions), count($quantities), count($unitPrices));
        $rawItems = [];
        $items = [];
        $hasNonBlankItem = false;

        for ($index = 0; $index < $rowCount; $index++) {
            $description = trim((string) ($descriptions[$index] ?? ''));
            $quantityValue = trim((string) ($quantities[$index] ?? ''));
            $unitPriceValue = trim((string) ($unitPrices[$index] ?? ''));

            $rawItems[] = [
                'description' => $description,
                'quantity' => $quantityValue,
                'unit_price' => $unitPriceValue,
            ];

            if ($description === '' && $quantityValue === '' && $unitPriceValue === '') {
                continue;
            }

            $hasNonBlankItem = true;
            $rowErrors = [];

            if ($description === '') {
                $rowErrors['description'] = 'Description is required.';
            } elseif (strlen($description) > 500) {
                $rowErrors['description'] = 'Description must be 500 characters or fewer.';
            }

            $quantityHundredths = $this->parseScaledDecimal($quantityValue);

            if ($quantityHundredths === null) {
                $rowErrors['quantity'] = 'Enter a valid quantity.';
            } elseif ($quantityHundredths <= 0) {
                $rowErrors['quantity'] = 'Quantity must be greater than zero.';
            }

            $unitPriceCents = $this->parseScaledDecimal($unitPriceValue);

            if ($unitPriceCents === null) {
                $rowErrors['unit_price'] = 'Enter a valid unit price.';
            }

            if ($rowErrors !== []) {
                $itemErrors[$index] = $rowErrors;
                continue;
            }

            try {
                $lineTotalCents = $this->money->lineTotal($quantityHundredths, $unitPriceCents);
            } catch (InvalidArgumentException $exception) {
                $itemErrors[$index] = [
                    'general' => 'This line total is too large.',
                ];
                continue;
            }

            $items[] = [
                'description' => $description,
                'quantity_hundredths' => $quantityHundredths,
                'unit_price_cents' => $unitPriceCents,
                'total_cents' => $lineTotalCents,
            ];
        }

        if (!$hasNonBlankItem) {
            $itemErrors['general'] = 'Add at least one line item.';
        }

        return [$rawItems, $items];
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }

    private function parseScaledDecimal(string $value): ?int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        if (preg_match('/^\d+(?:\.\d{1,2})?$/', $value) !== 1) {
            return null;
        }

        $parts = explode('.', $value, 2);
        $whole = ltrim($parts[0], '0');

        if ($whole === '') {
            $whole = '0';
        }

        if (strlen($whole) > 15) {
            return null;
        }

        $wholeValue = (int) $whole;

        if ($wholeValue > intdiv(PHP_INT_MAX - 99, 100)) {
            return null;
        }

        $fraction = str_pad($parts[1] ?? '', 2, '0');

        return ($wholeValue * 100) + (int) $fraction;
    }

    /**
     * @param array<int, array<string, mixed>> $clients
     */
    private function clientExists(array $clients, int $clientId): bool
    {
        foreach ($clients as $client) {
            if ((int) $client['id'] === $clientId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $profile
     */
    private function invoicePrefix(array $profile): string
    {
        $prefix = strtoupper(trim((string) ($profile['invoice_prefix'] ?? 'INV')));

        return preg_match('/^[A-Z0-9-]{1,20}$/', $prefix) === 1 ? $prefix : 'INV';
    }
}
