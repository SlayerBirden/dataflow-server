<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Authentication\Middleware;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\MessageInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;

/**
 * @codingStandardsIgnoreFile
 */
class TokenMiddlewareTest extends \Codeception\Test\Unit
{
    /**
     * @var TokenMiddleware
     */
    private $tokenMiddleware;
    /**
     * @var ObjectProphecy
     */
    private $requestHandler;
    /**
     * @var ObjectProphecy
     */
    private $tokenRepository;


    protected function setUp()
    {
        $this->tokenRepository = $this->prophesize(Selectable::class);
        $this->tokenMiddleware = new TokenMiddleware($this->tokenRepository->reveal());
        $this->requestHandler = $this->prophesize(RequestHandlerInterface::class);
    }

    public function test_Success_Proxy()
    {
        $request = new ServerRequest();

        $token = new Token();
        $token->setActive(true);
        $token->setDue(new \DateTime('+1 day'));
        $grant = new Grant();
        $grant->setResource('test');
        $token->setGrants(new ArrayCollection([$grant]));
        $token->setOwner(new User());

        $collection = $this->prophesize(\Doctrine\Common\Collections\Collection::class);
        $this->tokenRepository->matching(Argument::any())->willReturn($collection->reveal());
        $collection->count()->willReturn(1);
        $collection->first()->willReturn($token);

        $this->requestHandler->handle(Argument::type(ServerRequest::class))->shouldBeCalled();

        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('test');

        $this->tokenMiddleware->process(
            $request->withHeader('Authorization', 'Bearer XXX')
                    ->withAttribute(RouteResult::class, $routeResult->reveal()),
            $this->requestHandler->reveal()
        );
    }

    public function test_Success_Proxy_No_ACL()
    {
        $request = new ServerRequest();

        $token = new Token();
        $token->setActive(true);
        $token->setDue(new \DateTime('+1 day'));

        $collection = $this->prophesize(\Doctrine\Common\Collections\Collection::class);
        $this->tokenRepository->matching(Argument::any())->willReturn($collection->reveal());
        $collection->count()->willReturn(1);
        $collection->first()->willReturn($token);

        $this->requestHandler->handle(Argument::type(ServerRequest::class))->shouldBeCalled();

        $this->tokenMiddleware->process(
            $request->withHeader('Authorization', 'Bearer XXX'),
            $this->requestHandler->reveal()
        );
    }

    public function test_Empty_Authorization_Header()
    {
        $request = new ServerRequest();

        /** @var JsonResponse $response */
        $response = $this->tokenMiddleware->process(
            $request,
            $this->requestHandler->reveal()
        );

        /** @var MessageInterface $message */
        $message = $response->getPayload()['msg'];
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Empty Authorization header. Access denied.', $message->getMessage());
    }

    public function test_Can_Not_Find_Token()
    {
        $request = new ServerRequest();

        $token = new Token();
        $token->setActive(true);
        $token->setDue(new \DateTime('+1 day'));

        $collection = $this->prophesize(\Doctrine\Common\Collections\Collection::class);
        $this->tokenRepository->matching(Argument::any())->willReturn($collection->reveal());
        $collection->count()->willReturn(0);

        /** @var JsonResponse $response */
        $response = $this->tokenMiddleware->process(
            $request->withHeader('Authorization', 'Bearer XXX'),
            $this->requestHandler->reveal()
        );

        /** @var MessageInterface $message */
        $message = $response->getPayload()['msg'];
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Token is absent or invalid. Access denied.', $message->getMessage());
    }

    public function test_Token_Inactive()
    {
        $request = new ServerRequest();

        $token = new Token();
        $token->setActive(false);
        $token->setDue(new \DateTime('+1 day'));

        $collection = $this->prophesize(\Doctrine\Common\Collections\Collection::class);
        $this->tokenRepository->matching(Argument::any())->willReturn($collection->reveal());
        $collection->count()->willReturn(1);
        $collection->first()->willReturn($token);

        /** @var JsonResponse $response */
        $response = $this->tokenMiddleware->process(
            $request->withHeader('Authorization', 'Bearer XXX'),
            $this->requestHandler->reveal()
        );

        /** @var MessageInterface $message */
        $message = $response->getPayload()['msg'];
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Token is absent or invalid. Access denied.', $message->getMessage());
    }

    public function test_Token_Expired()
    {
        $request = new ServerRequest();

        $token = new Token();
        $token->setActive(true);
        $token->setDue(new \DateTime('-1 day'));

        $collection = $this->prophesize(\Doctrine\Common\Collections\Collection::class);
        $this->tokenRepository->matching(Argument::any())->willReturn($collection->reveal());
        $collection->count()->willReturn(1);
        $collection->first()->willReturn($token);

        /** @var JsonResponse $response */
        $response = $this->tokenMiddleware->process(
            $request->withHeader('Authorization', 'Bearer XXX'),
            $this->requestHandler->reveal()
        );

        /** @var MessageInterface $message */
        $message = $response->getPayload()['msg'];
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Token is absent or invalid. Access denied.', $message->getMessage());
    }

    public function test_Access_Not_Granted()
    {
        $request = new ServerRequest();

        $token = new Token();
        $token->setActive(true);
        $token->setDue(new \DateTime('+1 day'));
        $grant = new Grant();
        $grant->setResource('test2');
        $token->setGrants(new ArrayCollection([$grant]));

        $collection = $this->prophesize(\Doctrine\Common\Collections\Collection::class);
        $this->tokenRepository->matching(Argument::any())->willReturn($collection->reveal());
        $collection->count()->willReturn(1);
        $collection->first()->willReturn($token);

        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('test');

        /** @var JsonResponse $response */
        $response = $this->tokenMiddleware->process(
            $request->withHeader('Authorization', 'Bearer XXX')
                ->withAttribute(RouteResult::class, $routeResult->reveal()),
            $this->requestHandler->reveal()
        );

        /** @var MessageInterface $message */
        $message = $response->getPayload()['msg'];
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('The permission to resource is not granted.', $message->getMessage());
    }
}
