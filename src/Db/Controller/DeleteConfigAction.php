<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
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
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $deleted = false;
        $status = 200;

        try {
            $config = $this->entityManager->find(DbConfiguration::class, $id);
            //todo check owner
            if ($config) {
                $this->entityManager->remove($config);
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
                'configuration' => !empty($config) ? $this->extraction->extract($config) : null
            ],
        ], $status);
    }
}
