<?php

namespace Tests\Feature;

use App\Services\GitLabRepositoryService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class GitLabRepositoryServiceTest extends TestCase
{
    protected function tearDown(): void {
        Mockery::close();
    }

    public function test_search_repositories_success() {
        $mockClient = Mockery::mock(Client::class);

        $response = new Response(200, [], json_encode([
            [
                'name' => 'Repo1',
                'path_with_namespace' => 'User/Repo1',
                'description' => 'Description for Repo1',
                'namespace' => ['path' => 'user1']
            ],
            [
                'name' => 'Repo2',
                'path_with_namespace' => 'User/Repo2',
                'description' => 'Description for Repo2',
                'namespace' => ['path' => 'user2']
            ]
        ]));

        $mockClient->shouldReceive('request')
            ->once()
            ->with('GET', 'https://gitlab.com/api/v4/projects', ['query' => ['search' => 'test', 'per_page' => 5, 'order_by' => 'id', 'sort' => 'asc']])
            ->andReturn($response);

        $service = new GitLabRepositoryService($mockClient);
        $result = $service->searchRepositories(['q' => 'test']);

        $expected = [
            ['repository' => 'Repo1', 'full_repository_name' => 'User/Repo1', 'description' => 'Description for Repo1', 'creator' => 'user1'],
            ['repository' => 'Repo2', 'full_repository_name' => 'User/Repo2', 'description' => 'Description for Repo2', 'creator' => 'user2']
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_search_repositories_api_failure() {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('request')
            ->once()
            ->andThrow(new RequestException('API failure', Mockery::mock(RequestInterface::class)));

        $service = new GitLabRepositoryService($mockClient);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to contact API server.');

        $service->searchRepositories(['q' => 'test']);
    }

    public function test_format_repositories()
    {
        $mockClient = Mockery::mock(Client::class);
        $service = new GitLabRepositoryService($mockClient);

        $repositories = [
            [
                'name' => 'Repo1',
                'path_with_namespace' => 'User/Repo1',
                'description' => 'Description for Repo1',
                'namespace' => ['path' => 'user1']
            ]
        ];

        $expected = [
            [
                'repository' => 'Repo1',
                'full_repository_name' => 'User/Repo1',
                'description' => 'Description for Repo1',
                'creator' => 'user1'
            ]
        ];

        $this->assertEquals($expected, $service->formatRepositories($repositories));
    }
}
