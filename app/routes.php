<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use YaZabbixDashboard\Yazd;

return function (App $app) {

    # Why was withJson removed from Slim!?!?
    function json (Response $response, $data) {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');;
    }

    $app->get('/dashboard/{dashboard}/{token}', function (Request $request, Response $response, $args) {
        return $response
            ->withHeader('Location', (string)$request->getUri().'/')
            ->withStatus(302);
    });

    $app->get('/dashboard/{dashboard}/{token}/', function (Request $request, Response $response, $args) {
        # This could benefit from a better templating system!
        $html = file_get_contents(__DIR__ . '/../templates/default.html');
        $response->getBody()->write($html);
        return $response;
    });

    $app->get('/dashboard/{dashboard}/{token}/hostgroups.json', function (Request $request, Response $response, $args) {
        return json($response, [
            'hostgroups' => (new Yazd($args['dashboard'], $args['token']))->getHostGroups(),
        ]);
    });

    $app->get('/dashboard/{dashboard}/{token}/hostswithproblems.json', function (Request $request, Response $response, $args) {
        return json($response, [
            'hosts' => (new Yazd($args['dashboard'], $args['token']))->getHostsWithProblems(),
            'clocks' => (new Yazd($args['dashboard'], $args['token']))->getClocks(),
        ]);
    });
 
    $app->get('/dashboard/{dashboard}/{token}/problems.json', function (Request $request, Response $response, $args) {
        # This function isn't used for anything at the moment
        return json($response, [
            'problems' => (new Yazd($args['dashboard'], $args['token']))->getProblems(),
        ]);
    });
 
    $app->get('/dashboard/{dashboard}/{token}/clocks.json', function (Request $request, Response $response, $args) {
        # This function isn't used for anything at the moment
        return json($response, [
            'clocks' => (new Yazd($args['dashboard'], $args['token']))->getClocks(),
        ]);
    });

};
