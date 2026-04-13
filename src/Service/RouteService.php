<?php

namespace MartenaSoft\UserBundle\Service;

use MartenaSoft\CommonLibrary\Dictionary\RouteDictionary;
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

    public function rolesDetail(array $roles): array
    {
        $result = [];
        foreach (RouteDictionary::DESCRIPTION as $groupName => $role) {
            foreach ($roles as $name => $route) {
                if (($index = array_search($name, $role) ) !== false) {
                    $result[$groupName][$index . "( $name )"] = $name;
                    if (isset($roles[$name])) {
                        unset($roles[$name]);
                    }
                }
            }
        }

        if (!empty($roles)) {
            foreach ($roles as $name => $route) {
                $result[RouteDictionary::GROUP_UNTITLED_TITLE]["Untitled  ( $name )"] = $name;
            }
        }
        ksort($result);
        return $result;
    }
}
