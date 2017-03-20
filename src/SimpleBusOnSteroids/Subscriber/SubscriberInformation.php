<?php

namespace CleanCode\SimpleBusOnSteroids\Subscriber;

/**
 * Class SubscriberInformation
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SubscriberInformation
{
    const CLASS_NAME = 'class_name';
    const SUBSCRIBER_NAME = 'subscriber_name';

    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $name;

    /**
     * SubscriberInformation constructor.
     * @param string $className
     * @param string $name
     */
    private function __construct(string $className, string $name)
    {
        $this->className = $className;
        $this->name = $name;
    }

    /**
     * @param string $className
     * @param string $name
     * @return SubscriberInformation
     */
    public static function createWith(string $className, string $name) : self
    {
        return new self($className, $name);
    }

    /**
     * @param string $className
     * @return bool
     */
    public function hasClassName(string $className) : bool
    {
        return $this->className === $className;
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }
}