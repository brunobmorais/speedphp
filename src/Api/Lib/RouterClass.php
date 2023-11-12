<?php


namespace App\Api\Lib;

use Closure;
use Exception;
use ReflectionFunction;

class RouterClass
{
    /**
     * @var string
     */
    private $url = '';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var RequestClass
     */
    private $request;

    /**
     * @var string
     */
    private $group = '';

    private $contentType = 'application/json';

    public function __construct($url)
    {

        $this->request = new RequestClass($this);
        $this->url = $url;
        $this->setPrefix();

    }

    private function setContentType($contentType){
        $this->contentType = $contentType;
    }

    public function get($route, $params = [])
    {
        return $this->addRoute('GET', $route, $params);
    }

    public function post($route, $params = [])
    {
        return $this->addRoute('POST', $route, $params);
    }

    public function put($route, $params = [])
    {
        return $this->addRoute('PUT', $route, $params);
    }

    public function delete($route, $params = [])
    {
        return $this->addRoute('DELETE', $route, $params);
    }

    private function setPrefix()
    {
        $parseUrl = parse_url($this->url);
        $this->prefix = $parseUrl['path'] ?? '';
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    private function addRoute($method, $route, $params = [])
    {

        if (isset($this->group))
            $route = $this->group . $route;

        foreach ($params as $key => $value) {
            if ($value instanceof Closure) {
                $params['controller'] = $value;
                unset($params[$key]);
                continue;
            }
        }

        $params['middlewares'] = $params['middlewares']??[];

        $params['variables'] = [];

        $patternVariables = '/{(.*?)}/';
        if (preg_match_all($patternVariables, $route, $matches)) {
            $route = preg_replace($patternVariables, '(.*?)', $route);
            $params['variables'] = $matches[1];
        }

        $route = rtrim($route,'/');

        $patternRoute = '/^' . str_replace('/', '\/', $route) . '$/';

        $this->routes[$patternRoute][$method] = $params;

    }

    public function getUri()
    {
        $uri = $this->request->getUri();
        $xUri = strlen($this->prefix) ? explode($this->prefix, $uri) : [$uri];
        return rtrim(end($xUri),"/");
    }

    /**
     * @return array
     */
    private function getRoute()
    {
        $uri = $this->getUri();

        $httpMethod = $this->request->getHttpMethod();

        foreach ($this->routes as $patternRoute => $methods) {
            if (preg_match($patternRoute, $uri, $matches)) {
                if (isset($methods[$httpMethod])) {
                    unset($matches[0]);
                    $keys = $methods[$httpMethod]['variables'];
                    $methods[$httpMethod]['variables'] = array_combine($keys, $matches);
                    $methods[$httpMethod]['variables']['request'] = $this->request;
                    return $methods[$httpMethod];
                } else {
                    throw new Exception("Método não permitido", 405);
                }
            }
        }

        throw new Exception("URL não encontrada", 404);
    }

    /**
     * @return ResponseClass
     */
    public function run()
    {
        try {
            //throw new Exception("Pagina não encontrada",1);
            $route = $this->getRoute();
            if (!isset($route['controller'])) {
                throw new Exception("URL não pode ser processada", 500);
            }
            $args = [];

            $reflection = new ReflectionFunction($route['controller']);
            foreach ($reflection->getParameters() as $parameter) {
                $name = $parameter->getName();
                $args[$name] = $route['variables'][$name] ?? '';
            }

            return (new MiddlewareClass($route['middlewares'], $route['controller'], $args))->next($this->request);

        } catch (Exception $e) {

            return new ResponseClass($e->getCode(), $this->getErrorMessage($e), $this->contentType);
        }
    }

    private function getErrorMessage(Exception $e){
        switch ($this->contentType) {
            case "application/json":
                return [
                    "error" => true,
                    "message" => $e->getMessage()
                ];
            default:
                return $e->getMessage();
        }

    }


}