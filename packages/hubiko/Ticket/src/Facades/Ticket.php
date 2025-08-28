<?php

namespace Hubiko\Ticket\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hubiko\Ticket\Ticket
 */
class Ticket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ticket';
    }
} 