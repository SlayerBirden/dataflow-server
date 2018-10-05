<?php
declare(strict_types=1);

namespace DataFlow\Tests\Unit\Stdlib\Request;

use Psr\Http\Message\ServerRequestInterface;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use Zend\Diactoros\ServerRequest;

class A
{
    public $a;
    public $b;
}

final class ParserTest extends \Codeception\Test\Unit
{
    /**
     * @param ServerRequestInterface $request
     * @param array $expected
     *
     * @dataProvider getRequests
     */
    public function testGetRequestBody(ServerRequestInterface $request, array $expected): void
    {
        $this->assertSame($expected, Parser::getRequestBody($request));
    }

    public function getRequests(): array
    {
        $body = new A();
        $body->a = 'baz';
        $body->b = 'bar';

        $collection = new \ArrayObject([1, 2, 3]);
        return [
            [
                (new ServerRequest())->withParsedBody(null),
                [],
            ],
            [
                (new ServerRequest())->withParsedBody([]),
                [],
            ],
            [
                (new ServerRequest())->withParsedBody($body),
                ['a' => 'baz', 'b' => 'bar'],
            ],
            [
                (new ServerRequest())->withParsedBody($collection),
                [1, 2, 3],
            ],
            [
                (new ServerRequest())->withParsedBody(['zzz' => 'aaa']),
                ['zzz' => 'aaa'],
            ],
        ];
    }
}
