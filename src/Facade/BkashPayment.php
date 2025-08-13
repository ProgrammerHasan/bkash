<?php

namespace ProgrammerHasan\Bkash\Facade;

use Illuminate\Support\Facades\Facade;

class BkashPayment extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'BkashPayment';
    }
}
