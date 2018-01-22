<?php namespace Model;
/****        **************************************************************************** PERSON */
class Person extends \App\Model implements \App\Agent {
  const SRC  = '../data/model.xml';
  
  // const SRC = ['../data/model.xml', '/model/person/item'];
  
  protected function fixture(array $data): array  {
    return array_merge_recursive([
      '@title'   => '',
      '@id'      => '', // generate id with some sort of invokable/tostring object. new Slug($this, '@title') would have a __toString() that could turn title into an id based on input, otherwise it could be random/numeric.
      '@access'  => '',
      '@created' => time(),
      '@updated' => time(),
      'log'      => [] // should grab the fixture from log. maybe this needs to be static but then knowing about the instance would be difficult
    ], $data);
  }
  
  public function getName(\DOMElement $context) {
    return array_combine(['first','last'], explode(' ', $context['@title']));
  }
  
  public function sign(\App\Token $token) {
    return [$this['@access'], $this['@id']];
  }  
  
  public function contact(string $subject, string $message) {
    $key = getenv('EMAIL');
    $ch  = curl_init("https://api.postmarkapp.com/email");
    
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER	=> true,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json', 'Content-Type: application/json', "X-Postmark-Server-Token: {$key}"
      ],
      CURLOPT_POSTFIELDS => json_encode([
        'From' => getenv('SERVER_ADMIN'),
        'To'   => $this->context['@access'],
        'Subject'  => $subject,
        'HTMLBody' => $message,
      ]),
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
  }
  
}
