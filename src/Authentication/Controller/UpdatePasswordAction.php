<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Exception\PasswordRestrictionsException;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class UpdatePasswordAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        PasswordManagerInterface $passwordManager,
        HydratorInterface $hydrator
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->passwordManager = $passwordManager;
        $this->inputFilter = $inputFilter;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('password', $this->inputFilter);
        }
        $this->entityManager->beginTransaction();
        try {
            $pw = $this->updatePassword($data);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse([
                'data' => [
                    'password' => $this->hydrator->extract($pw),
                    'validation' => [],
                ],
                'success' => true,
                'msg' => new SuccessMessage('Password successfully updated.'),
            ], 200);
        } catch (PasswordRestrictionsException | \InvalidArgumentException $exception) {
            $this->entityManager->rollback();
            return new JsonResponse([
                'data' => [
                    'password' => null,
                    'validation' => [],
                ],
                'success' => false,
                'msg' => new DangerMessage($exception->getMessage()),
            ], 400);
        } catch (\Throwable $exception) {
            $this->logger->error((string)$exception);
            $this->entityManager->rollback();
            return new JsonResponse([
                'data' => [
                    'password' => null,
                    'validation' => [],
                ],
                'success' => false,
                'msg' => new DangerMessage('There was an error while updating password.'),
            ], 500);
        }
    }

    /**
     * @param array $data
     * @return Password
     * @throws PasswordRestrictionsException
     * @throws ORMException
     * @throws \Exception
     */
    private function updatePassword(array $data): Password
    {
        $userPasswords = $this->entityManager
            ->getRepository(Password::class)
            ->matching(
                Criteria::create()->where(
                    Criteria::expr()->eq('owner', $data[ClaimedResourceInterface::OWNER_PARAM])
                )
            );
        # check if mentioned
        /** @var Password $item */
        foreach ($userPasswords as $item) {
            if (password_verify($data['new_password'], $item->getHash())) {
                throw new PasswordRestrictionsException(
                    'You have already used this password before. Please use a new one.'
                );
            }
            if ($item->isActive()) {
                $item->setActive(false);
                $this->entityManager->persist($item);
            }
        }

        /** @var Password $password */
        $data['password'] = $data['new_password'];
        unset($data['new_password']);
        $data['created_at'] = (new \DateTime())->format(\DateTime::RFC3339);
        $data['due'] = (new \DateTime())->add(new \DateInterval('P1Y'))->format(\DateTime::RFC3339);
        $data['active'] = $data['active'] ?? true;

        $password = $this->hydrator->hydrate($data, new Password());
        $this->entityManager->persist($password);

        return $password;
    }
}
