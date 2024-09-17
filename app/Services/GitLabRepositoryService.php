<?php

namespace App\Services;

use App\Interfaces\RepositoryProvider;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GitLabRepositoryService implements RepositoryProvider {
    private Client $client;

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @param array $query
     * @throws \Exception
     * @return array|null
     */
    public function searchRepositories(array $query): ?array {
        try {
            $response = $this->client->request('GET', 'https://gitlab.com/api/v4/projects', [
                'query' => [
                    'search' => $query['q'],
                    'order_by' => 'id',
                    'sort' => 'asc',
                    'per_page' => 5
                ]
            ]);

            $repositories = json_decode($response->getBody()->getContents(), true);

            if ($repositories === null) {
                throw new Exception("Unable to parse JSON response.");
            }

            return $this->formatRepositories($repositories);
        } catch (GuzzleException $e) {
            throw new Exception("Unable to contact API server.", 500, $e);
        }
    }

    /**
     * @param array $repositories
     * @return array
     */
    public function formatRepositories(array $repositories): array {
        return array_map(function ($repository) {
            return [
                'repository' => $repository['name'],
                'full_repository_name' => $repository['path_with_namespace'],
                'description' => $repository['description'],
                'creator' => $repository['namespace']['path'],
            ];
        }, $repositories);
    }
}
