<?php

namespace App\Tests;

use App\Core\Router;

class RouterTest extends BaseTestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    public function testGetRouteIsRegistered(): void
    {
        $controller = 'App\\Controllers\\HomeController';
        $method = 'index';

        $this->router->get('/test', $controller, $method);

        $routes = $this->getRouterRoutes($this->router);

        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/test', $routes['GET']);
        $this->assertEquals([
            'controller' => $controller,
            'method' => $method
        ], $routes['GET']['/test']);
    }

    public function testPostRouteIsRegistered(): void
    {
        $controller = 'App\\Controllers\\AuthController';
        $method = 'login';

        $this->router->post('/test', $controller, $method);

        $routes = $this->getRouterRoutes($this->router);

        $this->assertArrayHasKey('POST', $routes);
        $this->assertArrayHasKey('/test', $routes['POST']);
        $this->assertEquals([
            'controller' => $controller,
            'method' => $method
        ], $routes['POST']['/test']);
    }

    public function testRouteDispatch(): void
    {
        // This test would require mocking controllers
        $this->expectNotToPerformAssertions(); // Placeholder test
    }

    private function getRouterRoutes(Router $router): array
    {
        $reflection = new \ReflectionClass($router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        return $routesProperty->getValue($router);
    }
}