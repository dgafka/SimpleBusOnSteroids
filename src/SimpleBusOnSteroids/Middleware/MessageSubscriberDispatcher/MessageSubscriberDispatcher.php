<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher;

use CleanCode\SimpleBusOnSteroids\ContextHolder;
use CleanCode\SimpleBusOnSteroids\Subscriber\SubscriberInformationHolder;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use SimpleBus\Message\Subscriber\Resolver\MessageSubscribersResolver;

/**
 * Class MessageHandler
 * @package CleanCode\SimpleBusOnSteroids\Middleware\MessageHandler
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class MessageSubscriberDispatcher implements MessageBusMiddleware
{
    const SUBSCRIBER_HANDLE_METHOD = 'handle';

    /**
     * @var MessageSubscribersResolver
     */
    private $messageSubscribersResolver;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var SubscriberInformationHolder
     */
    private $subscriberInformationHolder;
    /**
     * @var ContextHolder
     */
    private $contextHolder;
    /**
     * @var SubscriberHandledEventRepository
     */
    private $subscriberHandledEventRepository;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * MessageSubscriberDispatcher constructor.
     * @param ManagerRegistry $managerRegistry
     * @param MessageSubscribersResolver $messageSubscribersResolver
     * @param SubscriberInformationHolder $subscriberInformationHolder
     * @param ContextHolder $contextHolder
     * @param SubscriberHandledEventRepository $subscriberHandledEventRepository
     * @param Logger $logger
     */
    public function __construct(
        ManagerRegistry $managerRegistry, MessageSubscribersResolver $messageSubscribersResolver,
        SubscriberInformationHolder $subscriberInformationHolder, ContextHolder $contextHolder,
        SubscriberHandledEventRepository $subscriberHandledEventRepository, Logger $logger
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->messageSubscribersResolver = $messageSubscribersResolver;
        $this->subscriberInformationHolder = $subscriberInformationHolder;
        $this->contextHolder = $contextHolder;
        $this->subscriberHandledEventRepository = $subscriberHandledEventRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle($message, callable $next)
    {
        $messageSubscribers = $this->messageSubscribersResolver->resolve($message);
        $exception = null;

        $messageClass = get_class($message);
        $this->logger->addInfo("Handling " . $messageClass);
        foreach ($messageSubscribers as $messageSubscriber) {
            /** @var EntityManager $entityManager */
            $this->managerRegistry->resetManager();
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->beginTransaction();

            if (!array_key_exists(0, $messageSubscriber) || !$this->isMessageSubscriber($messageSubscriber[0])) {
                throw new \RuntimeException("Passed message subscriber doesn't have handle method");
            }

            $messageSubscriber = $messageSubscriber[0];
            try {
                $currentEventId = $this->contextHolder->currentContext()->currentlyHandledEventId();
                if (!$currentEventId) {
                    throw new \RuntimeException("Event with class name {$messageClass} doesn't have id. Probably it was published without Simple Bus On Steroids and can't be read");
                }

                $subscriberInformation = $this->subscriberInformationHolder->findFor($messageSubscriber);

                if (!$this->isAlreadyHandled($subscriberInformation, $currentEventId)) {
                    $messageSubscriber->{self::SUBSCRIBER_HANDLE_METHOD}($message);

                    $this->subscriberHandledEventRepository->save(SubscriberHandledEvent::createWithSubscriberInformation(
                        $subscriberInformation, $currentEventId
                    ));
                }

                $entityManager->flush();
                $entityManager->commit();
            }catch (\Exception $e) {
                $exception =  $e;

                $this->prepareDoctrineForNextUsage();
            }catch (\Throwable $e) {
                $exception = new \RuntimeException($e->getMessage(), $e->getCode(), $e);

                $this->prepareDoctrineForNextUsage();
            }
        }

        if ($exception) {
            throw $exception;
        }

        $next($message);
    }

    /**
     * @param $messageSubscriber
     * @return bool
     */
    private function isMessageSubscriber($messageSubscriber) : bool
    {
        return method_exists($messageSubscriber, self::SUBSCRIBER_HANDLE_METHOD);
    }

    /**
     * @param $subscriberInformation
     * @param $currentEventId
     * @return bool
     */
    private function isAlreadyHandled($subscriberInformation, $currentEventId) : bool
    {
        if (!$currentEventId) {
            true;
        }

        return (bool)$this->subscriberHandledEventRepository->findFor($subscriberInformation, $currentEventId);
    }

    private function prepareDoctrineForNextUsage()
    {
        $this->managerRegistry->getConnection()->rollback();
        $this->managerRegistry->getConnection()->close();
        $this->managerRegistry->getConnection()->connect();
    }
}