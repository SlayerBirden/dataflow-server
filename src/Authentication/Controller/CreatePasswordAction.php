<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Stdlib\Request\Parser;
use SlayerBirden\DataFlowServer\Stdlib\Validation\ResponseFactory;
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
     * @var EntityManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        EntityManagerRegistry $managerRegistry,
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
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = Parser::getRequestBody($request);
        $this->inputFilter->setData($data);

        if ($this->inputFilter->isValid()) {
            return $this->createPassword($data);
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
        $em->persist($password);
        $em->flush();
        $msg = 'Password has been successfully created!';
        return (new ResponseFactory())($msg, 200, 'password', $this->hydrator->extract($password));
    }
}
