<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\ClientRepository;

final class ClientController
{
    public function __construct(
        private readonly Config $config,
        private readonly ClientRepository $clients,
    ) {
    }

    public function index(): void
    {
        \render($this->config->basePath(), 'clients/index', [
            'title' => 'Clients',
            'active' => 'clients',
            'clients' => $this->clients->all(),
        ]);
    }

    public function create(): void
    {
        \render($this->config->basePath(), 'clients/create', [
            'title' => 'New Client',
            'active' => 'clients',
            'client' => $this->emptyClient(),
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $input = $this->inputFromRequest();
        $errors = $this->validate($input);

        if ($errors !== []) {
            http_response_code(422);

            \render($this->config->basePath(), 'clients/create', [
                'title' => 'New Client',
                'active' => 'clients',
                'client' => $input,
                'errors' => $errors,
            ]);

            return;
        }

        $this->clients->create($input);
        $_SESSION['flash_success'] = 'Client created successfully.';

        \redirect('/clients');
    }

    public function edit(string $id): void
    {
        $client = $this->findClient($id);

        if ($client === null) {
            $this->notFound();

            return;
        }

        \render($this->config->basePath(), 'clients/edit', [
            'title' => 'Edit Client',
            'active' => 'clients',
            'client' => $client,
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $client = $this->findClient($id);

        if ($client === null) {
            $this->notFound();

            return;
        }

        $input = $this->inputFromRequest();
        $errors = $this->validate($input);

        if ($errors !== []) {
            http_response_code(422);

            \render($this->config->basePath(), 'clients/edit', [
                'title' => 'Edit Client',
                'active' => 'clients',
                'client' => array_merge($client, $input),
                'errors' => $errors,
            ]);

            return;
        }

        $this->clients->update((int) $client['id'], $input);
        $_SESSION['flash_success'] = 'Client updated successfully.';

        \redirect('/clients');
    }

    public function destroy(string $id): void
    {
        $client = $this->findClient($id);

        if ($client === null) {
            $this->notFound();

            return;
        }

        if ($this->clients->hasInvoices((int) $client['id'])) {
            $_SESSION['flash_error'] = 'This client cannot be deleted while they have invoices.';

            \redirect('/clients');
        }

        if (!$this->clients->delete((int) $client['id'])) {
            $_SESSION['flash_error'] = 'The client could not be deleted.';

            \redirect('/clients');
        }

        $_SESSION['flash_success'] = 'Client deleted successfully.';

        \redirect('/clients');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findClient(string $id): ?array
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        return $this->clients->find($id);
    }

    /**
     * @return array<string, string>
     */
    private function inputFromRequest(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'company' => trim((string) ($_POST['company'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'tax_id' => trim((string) ($_POST['tax_id'] ?? '')),
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
            $errors['name'] = 'Client name is required.';
        } elseif (strlen($input['name']) > 255) {
            $errors['name'] = 'Client name must be 255 characters or fewer.';
        }

        if ($input['email'] !== '' && filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if (strlen($input['company']) > 255) {
            $errors['company'] = 'Company must be 255 characters or fewer.';
        }

        if (strlen($input['tax_id']) > 100) {
            $errors['tax_id'] = 'Tax ID must be 100 characters or fewer.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    private function emptyClient(): array
    {
        return [
            'name' => '',
            'email' => '',
            'company' => '',
            'address' => '',
            'tax_id' => '',
        ];
    }

    private function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Client not found.';
    }
}
