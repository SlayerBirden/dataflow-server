<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ValidationResponseFactory;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

final class CreatePasswordAction implements MiddlewareInterface
{
    /**
     * @var InputFilterInterface
     */
    private $inputFilter;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        ManagerRegistry $managerRegistry,
        InputFilterInterface $inputFilter,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->inputFilter = $inputFilter;
        $this->logger = $logger;
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

        if ($this->inputFilter->isValid()) {
            try {
                return $this->createPassword($data);
            } catch (\Exception $exception) {
                return (new GeneralErrorResponseFactory())('There was an error while creating password.', 'password');
            }
        } else {
            return (new ValidationResponseFactory())('password', $this->inputFilter);
        }
    }

    /**
     * @param array $data
     * @return ResponseInterface
     * @throws \Exception
     */
    private function createPassword(array $data): ResponseInterface
    {
        $data['created_at'] = (new \DateTime())->format(\DateTime::RFC3339);
        $data['due'] = (new \DateTime())->add(new \DateInterval('P1Y'))->format(\DateTime::RFC3339);
        $data['active'] = $data['active'] ?? true;
        $password = $this->hydrator->hydrate($data, new Password());
        $em = $this->managerRegistry->getManagerForClass(Password::class);
        if ($em === null) {
            throw new \LogicException('Could not retrieve EntityManager.');
        }
        $em->persist($password);
        $em->flush();
        $msg = 'Password has been successfully created!';
        return (new GeneralSuccessResponseFactory())($msg, 'password', $this->hydrator->extract($password));
    }
}
