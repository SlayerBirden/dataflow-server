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
use SlayerBirden\DataFlowServer\Doctrine\Middleware\ResourceMiddlewareInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

class DeleteConfigAction implements MiddlewareInterface
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
        $dbConfig = $request->getAttribute(ResourceMiddlewareInterface::DATA_RESOURCE);
        $deleted = false;
        $status = 200;

        try {
            if ($dbConfig) {
                $this->entityManager->remove($dbConfig);
                $this->entityManager->flush();
                $msg = new SuccessMessage('Configuration removed.');
                $deleted = true;
            } else {
                $msg = new DangerMessage('Could not find configuration to delete.');
                $status = 404;
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $msg = new DangerMessage('There was an error while removing configuration.');
            $status = 400;
        }

        return new JsonResponse([
            'msg' => $msg,
            'success' => $deleted,
            'data' => [
                'configuration' => !empty($dbConfig) ? $this->extraction->extract($dbConfig) : null
            ],
        ], $status);
    }
}
