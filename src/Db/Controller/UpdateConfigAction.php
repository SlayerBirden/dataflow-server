<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Doctrine\Exception\NonExistingEntity;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class UpdateConfigAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(
        EntityManagerInterface $entityManager,
        HydratorInterface $hydrator,
        LoggerInterface $logger,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->extraction = $extraction;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // todo check current user
        $data = $request->getParsedBody();
        $id = (int)$request->getAttribute('id');

        $message = null;
        $updated = false;
        $status = 200;

        try {
            $config = $this->getConfig($id, $data);
            $this->entityManager->persist($config);
            $this->entityManager->flush();
            $message = new SuccessMessage('Configuration has been updated!');
            $updated = true;
        } catch (NonExistingEntity $exception) {
            $message = new DangerMessage($exception->getMessage());
            $status = 404;
        } catch (ORMInvalidArgumentException $exception) {
            $message = new DangerMessage($exception->getMessage());
            $status = 400;
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            $message = new DangerMessage('Error while updating configuration.');
            $status = 400;
        }

        return new JsonResponse([
            'msg' => $message,
            'success' => $updated,
            'data' => [
                'configuration' => !empty($config) ? $this->extraction->extract($config) : null,
            ]
        ], $status);
    }

    private function getConfig(int $id, array $data): DbConfiguration
    {
        /** @var DbConfiguration $config */
        $config = $this->entityManager->find(DbConfiguration::class, $id);
        if (!$config) {
            throw new NonExistingEntity(sprintf('Could not find config by id %d.', $data['id']));
        }
        if (isset($data['id'])) {
            unset($data['id']);
        }
        // todo set current user as owner
        $this->hydrator->hydrate($data, $config);

        return $config;
    }
}
