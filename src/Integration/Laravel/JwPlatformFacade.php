<?php

namespace HubertNNN\JwPlatform\Integration\Laravel;

use Illuminate\Support\Facades\Facade;

class JwPlatformFacade extends Facade {
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'HubertNNN\JwPlatform\Contracts\JwPlatform'; // the IoC binding.
    }
}
