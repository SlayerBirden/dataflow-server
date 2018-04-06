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
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
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

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        PasswordManagerInterface $passwordManager,
        ExtractionInterface $extraction
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->passwordManager = $passwordManager;
        $this->extraction = $extraction;
        $this->inputFilter = $inputFilter;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(TokenMiddleware::USER_PARAM);

        if (empty($user)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('No active user detected.'),
            ], 403);
        }

        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            $newPassword = $data['new_password'] ?? null;

            if (empty($newPassword)) {
                return new JsonResponse([
                    'data' => [],
                    'success' => false,
                    'msg' => new DangerMessage('New password is not provided.'),
                ], 400);
            }

            $this->entityManager->beginTransaction();
            try {
                $pw = $this->updatePassword($user, $newPassword);
                $this->entityManager->flush();
                $this->entityManager->commit();

                return new JsonResponse([
                    'data' => [
                        'password' => $this->extraction->extract($pw),
                    ],
                    'success' => true,
                    'msg' => new SuccessMessage('Password successfully updated.'),
                ], 200);
            } catch (PasswordRestrictionsException $exception) {
                $this->entityManager->rollback();
                return new JsonResponse([
                    'data' => [],
                    'success' => false,
                    'msg' => new DangerMessage($exception->getMessage()),
                ], 400);
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $this->entityManager->rollback();
                return new JsonResponse([
                    'data' => [],
                    'success' => false,
                    'msg' => new DangerMessage('There was an error while updating password.'),
                ], 400);
            } catch (\Throwable $exception) {
                $this->logger->error((string)$exception);
                $this->entityManager->rollback();
                return new JsonResponse([
                    'data' => [],
                    'success' => false,
                    'msg' => new DangerMessage('Unknown error.'),
                ], 500);
            }
        } else {
            $validation = [];
            foreach ($this->inputFilter->getInvalidInput() as $key => $input) {
                $messages = $input->getMessages();
                $validation[] = [
                    'field' => $key,
                    'msg' => reset($messages)
                ];
            }

            return new JsonResponse([
                'data' => [
                    'validation' => $validation,
                ],
                'success' => false,
                'msg' => new DangerMessage('There were validation errors.'),
            ], 400);
        }
    }

    /**
     * @param User $user
     * @param string $password
     * @return Password
     * @throws PasswordRestrictionsException
     * @throws ORMException
     * @throws \Exception
     */
    private function updatePassword(User $user, string $password): Password
    {
        $hash = $this->passwordManager->getHash($password);
        $userPasswords = $this->entityManager
            ->getRepository(Password::class)
            ->matching(
                Criteria::create()->where(Criteria::expr()->eq('owner', $user))
            );
        # check if mentioned
        /** @var Password $item */
        foreach ($userPasswords as $item) {
            if ($hash === $item->getHash()) {
                throw new PasswordRestrictionsException('You have already used this password before. Please use a new one.');
            }
            if ($item->isActive()) {
                $item->setActive(false);
                $this->entityManager->persist($item);
            }
        }

        $now = new \DateTime();
        // password due in 1 Year
        $due = (new \DateTime())->add(new \DateInterval('P1Y'));

        $pw = new Password();
        $pw->setActive(true);
        $pw->setOwner($user);
        $pw->setCreatedAt($now);
        $pw->setDue($due);
        $pw->setHash($hash);
        $this->entityManager->persist($pw);

        return $pw;
    }
}
