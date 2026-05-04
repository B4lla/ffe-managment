<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class HashAwareEloquentUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        $credentials = array_filter(
            $credentials,
            fn ($key) => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if ($credentials === []) {
            return null;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'email') {
                $normalized = strtolower(trim((string) $value));
                $query->where('email_hash', hash('sha256', $normalized));
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }
}
