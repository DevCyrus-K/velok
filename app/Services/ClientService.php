<?php

namespace App\Services;

use App\Models\Customer;

class ClientService
{
    public function create(array $data): Customer
    {
        // Production cleanup: client/customer writes have a service entry point.
        return Customer::query()->create($data);
    }

    public function update(Customer $client, array $data): Customer
    {
        $client->update($data);

        return $client->refresh();
    }
}
