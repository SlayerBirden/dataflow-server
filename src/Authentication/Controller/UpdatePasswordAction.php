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
use SlayerBirden\DataFlowServer\Authentication\Hydrator\PasswordHydrator;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
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
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var PasswordHydrator
     */
    private $hydration;

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        PasswordManagerInterface $passwordManager,
        ExtractionInterface $extraction,
        PasswordHydrator $hydration
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->passwordManager = $passwordManager;
        $this->extraction = $extraction;
        $this->inputFilter = $inputFilter;
        $this->hydration = $hydration;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            $this->entityManager->beginTransaction();
            try {
                $pw = $this->updatePassword($data);
                $this->entityManager->flush();
                $this->entityManager->commit();

                return new JsonResponse([
                    'data' => [
                        'password' => $this->extraction->extract($pw),
                        'validation' => [],
                    ],
                    'success' => true,
                    'msg' => new SuccessMessage('Password successfully updated.'),
                ], 200);
            } catch (PasswordRestrictionsException | \InvalidArgumentException $exception) {
                $this->entityManager->rollback();
                $msg = new DangerMessage($exception->getMessage());
                $status = 400;
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $this->entityManager->rollback();
                $msg = new DangerMessage('There was an error while updating password.');
                $status = 500;
            } catch (\Throwable $exception) {
                $this->logger->error((string)$exception);
                $this->entityManager->rollback();
                $msg = new DangerMessage('Unknown app error.');
                $status = 500;
            }
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => $msg,
            ], $status);
        } else {
            return (new ValidationResponseFactory())('password', $this->inputFilter);
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
        $hash = $this->passwordManager->getHash($data['password']);
        $userPasswords = $this->entityManager
            ->getRepository(Password::class)
            ->matching(
                Criteria::create()->where(Criteria::expr()->eq('owner', $data['owner']))
            );
        # check if mentioned
        /** @var Password $item */
        foreach ($userPasswords as $item) {
            if ($hash === $item->getHash()) {
                throw new PasswordRestrictionsException(
                    'You have already used this password before. Please use a new one.'
                );
            }
            if ($item->isActive()) {
                $item->setActive(false);
                $this->entityManager->persist($item);
            }
        }

        return $this->hydration->hydrate($data, new Password());
    }
}
