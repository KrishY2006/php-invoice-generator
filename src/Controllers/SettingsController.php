<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\SettingsRepository;
use Throwable;

final class SettingsController
{
    private const ALLOWED_LOGO_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public function __construct(
        private readonly Config $config,
        private readonly SettingsRepository $settings,
    ) {
    }

    public function show(): void
    {
        render($this->config->basePath(), 'settings', [
            'title' => 'Business Settings',
            'active' => 'settings',
            'profile' => $this->settings->get(),
            'errors' => [],
            'maxLogoKb' => $this->config->maxLogoKb(),
        ]);
    }

    public function update(): void
    {
        $profile = $this->settings->get();
        $input = $this->inputFromRequest();
        $errors = $this->validate($input);

        $oldLogo = (string) ($profile['logo_path'] ?? '');
        $newLogo = null;

        if ($errors === []) {
            $newLogo = $this->storeUploadedLogo($errors);
        }

        if ($errors === []) {
            $input['logo_path'] = $newLogo ?? $oldLogo;

            try {
                $this->settings->save($input);
            } catch (Throwable $exception) {
                if ($newLogo !== null) {
                    $this->deleteLogo($newLogo);
                }

                throw $exception;
            }

            if ($newLogo !== null && $oldLogo !== '' && $oldLogo !== $newLogo) {
                $this->deleteLogo($oldLogo);
            }

            $_SESSION['flash_success'] = 'Business settings updated successfully.';

            redirect('/settings');
        }

        http_response_code(422);

        render($this->config->basePath(), 'settings', [
            'title' => 'Business Settings',
            'active' => 'settings',
            'profile' => array_merge($profile, $input),
            'errors' => $errors,
            'maxLogoKb' => $this->config->maxLogoKb(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function inputFromRequest(): array
    {
        $taxRate = trim((string) ($_POST['default_tax_rate'] ?? ''));

        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'tax_id' => trim((string) ($_POST['tax_id'] ?? '')),
            'bank_details' => trim((string) ($_POST['bank_details'] ?? '')),
            'currency' => strtoupper(trim((string) ($_POST['currency'] ?? ''))),
            'invoice_prefix' => trim((string) ($_POST['invoice_prefix'] ?? '')),
            'default_tax_rate' => $taxRate === '' ? '0.00' : $taxRate,
            'payment_terms' => trim((string) ($_POST['payment_terms'] ?? '')),
            'footer_note' => trim((string) ($_POST['footer_note'] ?? '')),
        ];
    }

    /**
     * @param array<string, string> $input
     * @return array<string, string>
     */
    private function validate(array $input): array
    {
        $errors = [];

        if ($input['name'] === '') {
            $errors['name'] = 'Business name is required.';
        } elseif (strlen($input['name']) > 255) {
            $errors['name'] = 'Business name must be 255 characters or fewer.';
        }

        if ($input['email'] !== '' && filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($input['website'] !== '' && filter_var($input['website'], FILTER_VALIDATE_URL) === false) {
            $errors['website'] = 'Enter a valid website URL.';
        }

        if ($input['currency'] === '') {
            $errors['currency'] = 'Currency is required.';
        } elseif (preg_match('/^[A-Z]{3}$/', $input['currency']) !== 1) {
            $errors['currency'] = 'Currency must be a 3-letter code such as USD.';
        }

        if (!is_numeric($input['default_tax_rate'])) {
            $errors['default_tax_rate'] = 'Default tax rate must be a number.';
        } elseif ((float) $input['default_tax_rate'] < 0 || (float) $input['default_tax_rate'] > 100) {
            $errors['default_tax_rate'] = 'Default tax rate must be between 0 and 100.';
        }

        if (strlen($input['invoice_prefix']) > 20) {
            $errors['invoice_prefix'] = 'Invoice prefix must be 20 characters or fewer.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $errors
     */
    private function storeUploadedLogo(array &$errors): ?string
    {
        $file = $_FILES['logo'] ?? null;

        if (!is_array($file)) {
            return null;
        }

        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            $errors['logo'] = $this->uploadErrorMessage($uploadError);

            return null;
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $errors['logo'] = 'The uploaded logo could not be verified.';

            return null;
        }

        $size = filesize($tmpName);
        $maxBytes = $this->config->maxLogoKb() * 1024;

        if ($size === false || $size <= 0) {
            $errors['logo'] = 'The uploaded logo is empty or unreadable.';

            return null;
        }

        if ($size > $maxBytes) {
            $errors['logo'] = sprintf(
                'The logo must be %d KB or smaller.',
                $this->config->maxLogoKb()
            );

            return null;
        }

        $imageInfo = @getimagesize($tmpName);

        if (!is_array($imageInfo) || !isset($imageInfo['mime']) || !is_string($imageInfo['mime'])) {
            $errors['logo'] = 'The uploaded file is not a valid image.';

            return null;
        }

        $mime = $imageInfo['mime'];

        if (!array_key_exists($mime, self::ALLOWED_LOGO_MIMES)) {
            $errors['logo'] = 'The logo must be a JPEG, PNG, WebP, or GIF image.';

            return null;
        }

        $directory = $this->config->logosPath();

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            $errors['logo'] = 'The logo directory could not be created.';

            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_LOGO_MIMES[$mime];
        $destination = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            $errors['logo'] = 'The logo could not be saved.';

            return null;
        }

        return $filename;
    }

    private function deleteLogo(string $filename): void
    {
        $filename = basename($filename);

        if ($filename === '') {
            return;
        }

        $path = $this->config->logosPath() . DIRECTORY_SEPARATOR . $filename;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The logo is too large for this server.',
            UPLOAD_ERR_PARTIAL => 'The logo upload was incomplete. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server upload directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded logo.',
            UPLOAD_ERR_EXTENSION => 'A server extension stopped the logo upload.',
            default => 'The logo upload failed.',
        };
    }
}
