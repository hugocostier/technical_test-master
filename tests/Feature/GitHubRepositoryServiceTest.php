<?php

namespace Tests\Feature;

use App\Services\GitHubRepositoryService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class GitHubRepositoryServiceTest extends TestCase
{
    protected function tearDown(): void {
        Mockery::close();
    }

    public function test_search_repositories_success() {
        $mockClient = Mockery::mock(Client::class);

        $response = new Response(200, [], json_encode([
            'items' => [
                [
                    'name' => 'Repo1',
                    'full_name' => 'User/Repo1',
                    'description' => 'Description for Repo1',
                    'owner' => ['login' => 'user1']
                ],
                [
                    'name' => 'Repo2',
                    'full_name' => 'User/Repo2',
                    'description' => 'Description for Repo2',
                    'owner' => ['login' => 'user2']
                ]
            ]
        ]));

        $mockClient->shouldReceive('request')
            ->once()
            ->with('GET', 'https://api.github.com/search/repositories', ['query' => ['q' => 'test', 'per_page' => 5]])
            ->andReturn($response);

        $service = new GitHubRepositoryService($mockClient);
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

        $service = new GitHubRepositoryService($mockClient);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to contact API server.');

        $service->searchRepositories(['q' => 'test']);
    }

    public function test_format_repositories()
    {
        $mockClient = Mockery::mock(Client::class);
        $service = new GitHubRepositoryService($mockClient);

        $repositories = [
            [
                'name' => 'Repo1',
                'full_name' => 'User/Repo1',
                'description' => 'Description for Repo1',
                'owner' => ['login' => 'user1']
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
