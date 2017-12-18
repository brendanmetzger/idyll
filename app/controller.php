<?php
namespace app;
abstract class Controller
{
  abstract public function authenticate(Request $request);
  abstract public function GETindex();
}