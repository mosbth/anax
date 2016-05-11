<?php

namespace Anax\App;

/**
 * Anax base class for an application.
 *
 */
class CAnaxDefault
{
    use \Anax\DI\TInjectable;



    /**
     * Construct.
     *
     * @param array $di dependency injection of service container.
     */
    public function __construct($di)
    {
        $this->di = $di;
    }
}
