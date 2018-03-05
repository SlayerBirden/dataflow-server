<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

class GetConfigAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $success = false;
        $msg = '';
        $status = 200;

        try {
            $config = $this->entityManager
                ->getRepository(DbConfiguration::class)
                ->find($id);
            if ($config !== null) {
                $success = true;
            } else {
                $msg = new DangerMessage('Could not find user.');
                $status = 404;
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = new DangerMessage('Could not find user. An error occurred.');
            $status = 500;
        }

        return new JsonResponse([
            'data' => [
                'configuration' => !empty($config) ? $this->extraction->extract($config) : $config,
            ],
            'success' => $success,
            'msg' => $msg,
        ], $status);
    }
}
