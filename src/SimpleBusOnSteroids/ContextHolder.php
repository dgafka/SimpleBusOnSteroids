<?php

namespace CleanCode\SimpleBusOnSteroids;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ContextHolder
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @DI\Service(shared=true, id="simple_bus_context_holder")
 */
class ContextHolder
{
    /** @var  Context */
    private $currentContext;

    /**
     * @param Context $context
     */
    public function setCurrentContext(Context $context)
    {
        $this->currentContext = $context;
    }

    /**
     * @return Context
     */
    public function currentContext() : Context
    {
        return $this->currentContext;
    }
}