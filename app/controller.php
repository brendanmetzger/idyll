<?php
namespace app;
abstract class Controller
{
  abstract public function authenticate(Request $request);
  abstract public function GETindex();
  
  
  public function GETlogin()
  {
    # code...
  }
  
  public function POSTlogin()
  {
    # code...
  }
  
  public function GETlogout()
  {
    # code...
  }
}