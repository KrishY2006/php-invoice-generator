<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;
use Throwable;

final class PdfRenderer
{
    private const ALLOWED_LOGO_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     */
    public function stream(array $invoice, array $items): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setChroot($this->basePath);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->render($invoice, $items), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream($this->filename($invoice), [
            'Attachment' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     */
    public function render(array $invoice, array $items): string
    {
        $template = $this->basePath . '/templates/pdf/invoice.php';

        if (!is_file($template)) {
            throw new RuntimeException(sprintf('PDF template not found: %s', $template));
        }

        if (!function_exists('e')) {
            require_once dirname(__DIR__) . '/helpers.php';
        }

        $business = $invoice['business'] ?? [];
        $logoDataUri = is_array($business)
            ? $this->logoDataUri((string) ($business['logo_path'] ?? ''))
            : '';

        ob_start();

        try {
            require $template;

            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $invoice
     */
    private function filename(array $invoice): string
    {
        $number = (string) ($invoice['invoice_number'] ?? 'invoice');
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $number);

        if (!is_string($safe) || trim($safe, '-.') === '') {
            $safe = 'invoice';
        }

        return $safe . '.pdf';
    }

    private function logoDataUri(string $logoPath): string
    {
        $filename = basename($logoPath);

        if ($filename === '') {
            return '';
        }

        $logoDirectory = realpath($this->basePath . '/storage/logos');
        $fullPath = realpath($this->basePath . '/storage/logos/' . $filename);

        if (
            $logoDirectory === false
            || $fullPath === false
            || !str_starts_with($fullPath, $logoDirectory . DIRECTORY_SEPARATOR)
            || !is_file($fullPath)
        ) {
            return '';
        }

        $imageInfo = @getimagesize($fullPath);
        $mime = is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])
            ? $imageInfo['mime']
            : null;

        if ($mime === null || !in_array($mime, self::ALLOWED_LOGO_MIMES, true)) {
            return '';
        }

        $contents = @file_get_contents($fullPath);

        if ($contents === false) {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}
