<?php

namespace CleanCode\SimpleBusOnSteroids;

/**
 * Class ContextHolder
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
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