<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Exception\PasswordRestrictionsException;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class UpdatePasswordAction implements MiddlewareInterface
{
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
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var Selectable
     */
    private $passwordRepository;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Selectable $passwordRepository,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        PasswordManagerInterface $passwordManager,
        HydratorInterface $hydrator
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->passwordRepository = $passwordRepository;
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
        if (!is_array($data)) {
            return (new DataValidationResponseFactory())('password');
        }
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return (new ValidationResponseFactory())('password', $this->inputFilter);
        }
        $em = $this->managerRegistry->getManagerForClass(Password::class);
        if ($em === null) {
            return (new GeneralErrorResponseFactory())('Could not retrieve ObjectManager', 'password');
        }
        if (!($em instanceof EntityManagerInterface)) {
            return (new GeneralErrorResponseFactory())('Could not use current ObjectManager', 'password');
        }
        $em->beginTransaction();
        try {
            $pw = $this->updatePassword($data);
            $em->flush();
            $em->commit();

            $msg = 'Password successfully updated.';
            return (new GeneralSuccessResponseFactory())($msg, 'password', $this->hydrator->extract($pw));
        } catch (PasswordRestrictionsException | \InvalidArgumentException $exception) {
            $em->rollback();
            return (new GeneralErrorResponseFactory())($exception->getMessage(), 'password', 400);
        } catch (\Exception $exception) {
            $this->logger->error((string)$exception);
            $em->rollback();
            return (new GeneralErrorResponseFactory())('There was an error while updating password.', 'password', 400);
        }
    }

    /**
     * @param array $data
     * @return Password
     * @throws PasswordRestrictionsException
     * @throws \Exception
     */
    private function updatePassword(array $data): Password
    {
        $userPasswords = $this->passwordRepository->matching(
            Criteria::create()->where(
                Criteria::expr()->eq('owner', $data[ClaimedResourceInterface::OWNER_PARAM])
            )
        );
        # check if mentioned
        $em = $this->managerRegistry->getManagerForClass(Password::class);
        /** @var Password $item */
        foreach ($userPasswords as $item) {
            if (password_verify($data['new_password'], $item->getHash())) {
                throw new PasswordRestrictionsException(
                    'You have already used this password before. Please use a new one.'
                );
            }
            if ($item->isActive()) {
                $item->setActive(false);
                $em->persist($item);
            }
        }

        /** @var Password $password */
        $data['password'] = $data['new_password'];
        unset($data['new_password']);
        $data['created_at'] = (new \DateTime())->format(\DateTime::RFC3339);
        $data['due'] = (new \DateTime())->add(new \DateInterval('P1Y'))->format(\DateTime::RFC3339);
        $data['active'] = $data['active'] ?? true;

        $password = $this->hydrator->hydrate($data, new Password());
        $em->persist($password);

        return $password;
    }
}
