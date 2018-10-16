<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Stdlib\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Middleware\ResetIdMiddleware;
use Zend\Diactoros\Response\JsonResponse;

final class ResetIdMiddlewareTest extends TestCase
{
    public function testResetData()
    {
        $middleware = new ResetIdMiddleware();

        $request = new \Zend\Diactoros\ServerRequest();

        $pipeline = new \Zend\Stratigility\MiddlewarePipe();
        $last = new class implements MiddlewareInterface
        {
            /**
             * @inheritdoc
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return new JsonResponse($request->getParsedBody(), 200);
            }
        };
        $pipeline->pipe($middleware);
        $pipeline->pipe($last);

        $response = $pipeline->handle($request->withParsedBody([
            'name' => 'bar',
            'id' => 'baz',
        ]));

        $this->assertJsonStringEqualsJsonString(json_encode(['name' => 'bar']), $response->getBody()->__toString());
    }

    public function testResetDataNonDefault()
    {
        $middleware = new ResetIdMiddleware('name');

        $request = new \Zend\Diactoros\ServerRequest();

        $pipeline = new \Zend\Stratigility\MiddlewarePipe();
        $last = new class implements MiddlewareInterface
        {
            /**
             * @inheritdoc
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return new JsonResponse($request->getParsedBody(), 200);
            }
        };
        $pipeline->pipe($middleware);
        $pipeline->pipe($last);

        $response = $pipeline->handle($request->withParsedBody([
            'name' => 'bar',
            'id' => 'baz',
        ]));

        $this->assertJsonStringEqualsJsonString(json_encode(['id' => 'baz']), $response->getBody()->__toString());
    }
}
