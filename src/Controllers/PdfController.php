<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Services\InvoiceDocument;
use App\Services\PdfRenderer;

final class PdfController
{
    public function __construct(
        private readonly Config $config,
        private readonly InvoiceDocument $documents,
        private readonly PdfRenderer $pdfRenderer,
    ) {
    }

    public function download(string $id): void
    {
        if (preg_match('/^\d+$/', $id) !== 1) {
            $this->notFound();

            return;
        }

        $invoiceId = (int) $id;

        if ($invoiceId <= 0) {
            $this->notFound();

            return;
        }

        $invoice = $this->documents->find($invoiceId);

        if ($invoice === null) {
            $this->notFound();

            return;
        }

        $items = is_array($invoice['items'] ?? null) ? $invoice['items'] : [];

        $this->pdfRenderer->stream($invoice, $items);
    }

    private function notFound(): void
    {
        http_response_code(404);

        render($this->config->basePath(), 'errors/404', [
            'title' => 'Invoice Not Found',
            'active' => 'invoices',
            'message' => 'The requested invoice could not be found.',
        ]);
    }
}
