<?php

namespace CleanCode\SimpleBusOnSteroids\Subscriber;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class SubscriberInformationHolder
 * @package CleanCode\SimpleBusOnSteroids\Subscriber
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 *
 * @DI\Service(id="simple_bus_subscriber_subscriber_information_holder")
 */
class SubscriberInformationHolder
{
    /**
     * @var array|SubscriberInformation[]
     */
    private $subscribersInformation = [];

    /**
     * SubscriberInformationHolder constructor.
     * @param array|string[] $subscribersInformation
     *
     * @DI\InjectParams({
     *      "subscribersInformation" = @DI\Inject("%empty_array%")
     * })
     */
    public function __construct(array $subscribersInformation)
    {
        $this->initialize($subscribersInformation);
    }

    /**
     * @param string $object
     * @return SubscriberInformation
     */
    public function findFor($object) : SubscriberInformation
    {
        $className = get_class($object);

        foreach ($this->subscribersInformation as $subscriberInformation) {
            if ($subscriberInformation->hasClassName($className)) {
                return $subscriberInformation;
            }
        }

        throw new \RuntimeException("There is no subscriber information for {$className}");
    }

    /**
     * @param array|string[] $subscribersInformation
     */
    private function initialize(array $subscribersInformation)
    {
        foreach ($subscribersInformation as $subscriberInformation) {
            $this->subscribersInformation[] = SubscriberInformation::createWith(
                $subscriberInformation[SubscriberInformation::CLASS_NAME],
                $subscriberInformation[SubscriberInformation::SUBSCRIBER_NAME]
            );
        }
    }
}