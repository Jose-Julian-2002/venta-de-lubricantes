<?php

namespace App\Services;

use App\Models\Cliente;

class ClienteService
{
    /** @param array{nombre: string, nit_ci?: string|null, telefono?: string|null} $data */
    public function create(array $data): Cliente
    {
        return Cliente::query()->create($data);
    }
}
