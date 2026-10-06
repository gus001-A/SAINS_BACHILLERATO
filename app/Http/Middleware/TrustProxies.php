<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * En producción el sitio va detrás de un proxy que termina el SSL, así que
     * hay que confiar en `X-Forwarded-Proto` para que Laravel sepa que la
     * petición es HTTPS. Si no, route()/redirect() generan enlaces http:// y la
     * redirección http→https posterior pierde la cabecera X-Inertia (y el login
     * termina mostrando el dashboard dentro de un modal).
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
