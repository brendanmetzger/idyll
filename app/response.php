<?php
namespace app;

class Response {
  private $request;
  public function __construct(Request $request)
  {
    $this->request = $request;
  }
  
  public function __toString()
  {
    return $this->request->respond();
  }
}