<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\InputFilter\InputFilterInterface;

final class InputFilterMiddleware implements MiddlewareInterface
{
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var string
     */
    private $refName;

    public function __construct(InputFilterInterface $inputFilter, string $refName)
    {
        $this->inputFilter = $inputFilter;
        $this->refName = $refName;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())($this->refName, $this->inputFilter);
        }

        $values = $this->inputFilter->getValues();
        // only pass values that exist in data
        $toPass = array_filter($values, function ($key) use ($data) {
            return array_key_exists($key, $data);
        }, ARRAY_FILTER_USE_KEY);

        return $handler->handle($request->withParsedBody($toPass));
    }
}
