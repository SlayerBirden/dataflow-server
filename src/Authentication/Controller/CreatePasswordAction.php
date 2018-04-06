<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;
use Zend\InputFilter\InputFilterInterface;

class CreatePasswordAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ExtractionInterface
     */
    private $extraction;
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;

    public function __construct(
        EntityManager $entityManager,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        ExtractionInterface $extraction,
        PasswordManagerInterface $passwordManager
    ) {
        $this->entityManager = $entityManager;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
        $this->extraction = $extraction;
        $this->passwordManager = $passwordManager;
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

        if ($this->hasActivePassword($user)) {
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('You already have active password. Please use "update" routine.'),
            ], 412);
        }

        $data = $request->getParsedBody();
        $this->inputFilter->setData($data);

        $message = null;
        $validation = [];
        $created = false;
        $status = 400;

        if ($this->inputFilter->isValid()) {
            try {
                $pw = $data['password'] ?? null;

                if ($pw === null) {
                    $message = new DangerMessage('No password provided.');
                } else {
                    $password = new Password();
                    $password->setHash($this->passwordManager->getHash($pw));
                    $password->setOwner($user);

                    $now = new \DateTime();
                    // password due in 1 Year
                    $due = (new \DateTime())->add(new \DateInterval('P1Y'));
                    $password->setCreatedAt($now);
                    $password->setDue($due);
                    $password->setActive(true);

                    $this->entityManager->persist($password);
                    $this->entityManager->flush();
                    $message = new SuccessMessage('Password has been successfully created!');
                    $created = true;
                    $status = 200;
                }
            } catch (ORMInvalidArgumentException $exception) {
                $message = new DangerMessage($exception->getMessage());
            } catch (ORMException $exception) {
                $this->logger->error((string)$exception);
                $message = new DangerMessage('There was an error creating password.');
            }
        } else {
            $message = new DangerMessage('There were validation errors.');
            foreach ($this->inputFilter->getInvalidInput() as $key => $input) {
                $messages = $input->getMessages();
                $validation[] = [
                    'field' => $key,
                    'msg' => reset($messages)
                ];
            }
        }

        return new JsonResponse([
            'msg' => $message,
            'success' => $created,
            'data' => [
                'validation' => $validation,
                'password' => !empty($password) ? $this->extraction->extract($password) : null,
            ]
        ], $status);
    }

    private function hasActivePassword(User $user): bool
    {
        $collection = $this->entityManager
            ->getRepository(Password::class)
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('owner', $user))
                    ->andWhere(Criteria::expr()->eq('active', false))
            );

        return !$collection->isEmpty();
    }
}
