<?php

  class authenticated_REST_Controller extends ifx_REST_Controller
  {
      public $token = false;

      public function __construct()
      {
          parent::__construct();

          if (isset($_SERVER['Authorization'])) {
              $bearer = trim($_SERVER["Authorization"]);
          } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
              $bearer = trim($_SERVER["HTTP_AUTHORIZATION"]);
          } elseif (function_exists('apache_request_headers')) {
              $requestHeaders = apache_request_headers();
              // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
              $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
              //print_r($requestHeaders);
              if (isset($requestHeaders['Authorization'])) {
                  $bearer = trim($requestHeaders['Authorization']);
              }
          }

          if (isset($bearer)) {
              $this->token = str_replace('Bearer ', '', $bearer);
          }

          if (($this->token = JWT::validateToken($this->token)) === false) {
              return $this->response([], ifx_REST_Controller::HTTP_UNAUTHORIZED);
          }
      }
  }
