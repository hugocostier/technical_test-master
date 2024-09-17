<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RepositoryRequest;
use App\Interfaces\RepositoryProvider;

class RepositoryController extends Controller
{
    private array $providers;

    /**
     * @param \App\Interfaces\RepositoryProvider[] $providers
     */
    public function __construct(RepositoryProvider ...$providers) {
        $this->providers = $providers;
    }

    /**
     * @param \App\Http\Requests\RepositoryRequest $request
     * @return array<array>
     */
    public function search(RepositoryRequest $request): array {
        $query = $request->validated();
        $results = [];

        foreach($this->providers as $provider) {
            $results = array_merge($results, $provider->searchRepositories($query));
        }

        return $results;
    }
}
