<?php namespace App;

/****       **************************************************************************************/
class Token {
  private $algo, $size, $secret;
  public  $expire, $version, $secure = true;
          
  public function __construct(array $config, ?int $expire = null) {
    [$this->algo, $this->size, $this->secret, $this->version] = $config;
    $this->secure = $this->version !== 'local';
    $this->expire = ($expire !== null ? $expire : 3600 * 24 * 90) + time();
  }
  
  public function validate(string $hash, string $private, string $public) {
    return hash_equals($hash, $this->encode($private, $public));
  }
  
  public function encode(string $key, string $msg): string {
    return array_reduce(str_split(str_rot13($msg)), \Closure::bind(function($H, $L)  {
      return substr_replace($H, $L, $this->i -= $this->s, 0);
    }, (object)['s'=>floor($this->size/strlen($msg)),'i'=>0]), hash_hmac($this->algo, $key, $this->secret));
  }
  
  public function decode(string $msg): string {
    $size = strlen($msg) - 32;
    $skip = floor($this->size / $size) * -1;
    return str_rot13(array_reduce(range(1, $size), function($o, $i) use($msg, $skip) {
      return $o . $msg[($i * $skip - 1)];
    }, ''));
  }
  
}

/*************        ****************************************************************************/
abstract class Method {
  
  abstract public function session(Token $token, array $set = null);
  
  public $start, $route, $scheme = 'http', $format = 'txt', $params, $data;
  
  static public function New(string $method) {
    $ClassName = "\\App\\{$method}";
    return new $ClassName($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true) );
  }
  
  public function __toString(): string {
    return substr(static::class, 4);
  }
}

/****     ****************************************************************************************/
class CLI extends Method {
  public $scheme = 'console';
  public function __construct($timestamp) {
    $this->start = $timestamp;
    $this->route  = array_filter(explode(':', $_SERVER['argv'][1] ?? ''));
    $this->params = array_slice($_SERVER['argv'], 2);
  }
  
  public function session(Token $token, array $set = null) {
    return null;
  }
}

/****     ****************************************************************************************/
class GET extends Method {
  public $direct = false;
  public function __construct(float $timestamp) {    
    $this->start  = $timestamp;
    $this->route  = array_filter($_GET['_r_']);
    $this->params = array_filter(explode('/', $_GET['_p_']));
    $this->format = $_GET['_e_'] ?: 'html';
    $this->host   = sprintf('%s://%s', getenv('REQUEST_SCHEME'), getenv('SERVER_NAME'));
    $this->direct = stripos(getenv('HTTP_REFERER'), $this->host) === 0;
    $this->path   = getenv('REQUEST_URI');
  }
  
  public function session(Token $Tok, array $set = null) {
    if ($set) {
      setcookie($Tok->version, $Tok->encode(...$set), $Tok->expire, '/', '', $Tok->secure, true);
    }
    return $_COOKIE[$Tok->version];
  }
}

/****      ***************************************************************************************/
class POST extends GET {
  
  public function __construct(float $timestamp) {
    parent::__construct($timestamp);
    array_unshift($this->params, new Data($_POST));
  }
}

/****         ************************************************************************************/
class Request {

  public $handlers = [], $token;

  public function __construct(Method $method) {
    $this->method = $method;
    $this->token  = new Token(ID);
  }
    
  public function handle (string $scheme, callable $callback): void {
    $this->handlers[$scheme] = $callback;
  }

  // TODO this is still bothering me.
  public function authenticate(\ReflectionMethod $method): bool {
    if ($hash = $this->method->session($this->token)) {
      $Model = (string) $method->getParameters()[0]->getType();
      $user  = new $Model( $this->token->decode($hash) );
      $valid = $this->token->validate($hash, ...$user->sign($this->token));
      // valid should be - instanceof 'something...'
      $method->setAccessible($valid);
      array_unshift($this->method->params, $user); 
      return $valid;
    }
    return false;
  }
  
  public function respond(?string $type = null) {
    return $this->handlers[$type ?: $this->method->scheme]->call($this);
  }
  
  public function delegate(...$route) {
    $this->method->route = array_replace($route, $this->method->route);
    [$instance, $method] = Controller::Make($this, ...$this->method->route);
    return $instance->output($method->invokeArgs($instance, $this->method->params));
  }
}

/* TODO
[ ] Consider implementing a way users and guests can see same page, with users having rendered session vars
[ ] response should be in control of filtering/reordering DOM presentation
[ ] response should be in charge of caching
[ ] response should be able to return a partial if request is ajax.
*/
/****          ***********************************************************************************/
class Response {
  
  private $request, $template, $content, $output;

  public $content_type = [
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
    'incorrect'    => '404',
  ];
  
  public function __construct(Request $request) {
    $this->request = $request;
  }
  
  public function redirect($location_url, $code = 302) {
    header("Cache-Control: no-<text, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: {$location_url}", false, $code);
    exit();
  }
  
  public function authorize(string $type, string $message, string $key) {
    $id    = $this->request->token->decode($message);
    $token = $this->request->token;
    if ($token->validate($message, $key, $id)) {
      
      $this->request->method->session($token, Factory::Model($type)->newInstance($id)->sign($token));
      $this->redirect('/');
    }
  }
  
  public function setHeader() {
    # code...
  }
  
  public function setTemplate(View $view): void {
    $this->template = $view;
  }
  
  // object can be a domdocument, or view object, should be way to set more than one piece of content
  public function setContent($object): void {
    $this->content = $object;
  }
  
  public function compose($data = []) {
    return $this->output = ($this->template ? $this->template->set('content', $this->content)->render($data) : $this->content);
  }
  
  public function __toString()
  {
    return (string) $this->output;
  }
}