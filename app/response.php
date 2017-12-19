<?php
namespace app;

class Response {
  
  private $request;
  
  public $content = [
    'html' => 'Content-Type: application/xhtml+xml; charset=utf-8',
    'json' => 'Content-Type: application/javascript; charset=utf-8',
    'xml'  => 'Content-Type: text/xml; charset=utf-8',
    'svg'  => 'Content-Type: image/svg+xml; charset=utf-8',
    'jpg'  => 'Content-Type: image/jpeg',
    'js'   => 'Content-Type: application/javascript; charset=utf-8',
    'css'  => 'Content-Type: text/css; charset=utf-8'
  ];
  
  public $status = [
    'unauthorized' => 'HTTP/1.0 401 Unauthorized',
    'incorrect' => '404',
  ];
  
  static public function redirect($location_url, $code = 302)
  {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: {$location_url}", false, $code);
    exit();
  }
  
  public function __construct(Request $request)
  {
    $this->request = $request;
  }
  
  public function package()
  {
    // set headers (if HTTP);
    return $this->request->respond();
  }
  
  public function __toString()
  {
    return $this->package();
  }
}