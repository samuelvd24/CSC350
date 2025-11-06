<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Response;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

function route($method, $pattern) {
  return $_SERVER['REQUEST_METHOD'] === $method && preg_match($pattern, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $GLOBALS['m']);
}

if (route('GET', '#^/api/health$#'))      { echo json_encode(['ok' => true]); exit; }
if (route('POST', '#^/api/auth/login$#')) { (new App\Controllers\AuthController)->login(); exit; }
if (route('GET', '#^/api/cars$#'))        { (new App\Controllers\CarsController)->index(); exit; }
if (route('POST', '#^/api/rentals$#'))    { (new App\Controllers\RentalsController)->create(); exit; }

http_response_code(404); echo json_encode(['error' => 'Not Found']);
