<?php

namespace MartenaSoft\UserBundle\Service;

use Symfony\Component\Routing\RouterInterface;

class RouteService
{
    public function __construct(private RouterInterface $router)
    {

    }

    public function list(?string $filterPregMatch = null, ?string $field = null): array
    {
        $routeCollection = $this->router->getRouteCollection();

        $routes = [];
        foreach ($routeCollection as $name => $route) {
            if (!empty($filterPregMatch) && !preg_match($filterPregMatch, $name, $match)) {
                continue;
            }

            $routes[$name] = [
                'name' => $name,
                'path' => $route->getPath(),
                'methods' => implode(', ', $route->getMethods()),
            ];
        }
        return $routes;
    }
}
