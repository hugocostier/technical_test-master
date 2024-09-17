<?php

namespace App\Interfaces;

interface RepositoryProvider {
    /**
     * @param array $query
     * @return array|null
     */
    public function searchRepositories(array $query): ?array;

    /**
     * @param array $repositories
     * @return array
     */
    public function formatRepositories(array $repositories): array;
}
