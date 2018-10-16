<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Stdlib\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;

final class ResetIdMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $idName;

    public function __construct(string $idName = 'id')
    {
        $this->idName = $idName;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);
        if (isset($data[$this->idName])) {
            unset($data[$this->idName]);
        }

        return $handler->handle($request->withParsedBody($data));
    }
}
