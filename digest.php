<?php
$url = 'http://domain.foo';
$uri = '/assets';
$username = 'testyser';
$password = 'somepassword';
$method   = 'GET';
$ht = new HttpRequest($url.$uri, HttpRequest::METH_GET);
$ht->send();
print_r($ht->getRawRequestMessage());
print_r($ht->getRawResponseMessage());
$auth_resp_header = $ht->getResponseHeader('WWW-Authenticate');
$auth_resp_header = explode(',', preg_replace("/^Digest/i", "", $auth_resp_header));
$auth_pieces = array();
foreach ($auth_resp_header as &$piece) {
    $piece = trim($piece);
    $piece = explode('=', $piece);
    $auth_pieces[$piece[0]] = trim($piece[1], '"');
}
print_r($auth_pieces);
// build response digest
$nc = str_pad('1', 8, '9', STR_PAD_LEFT);
$cnonce = '0a4f113b';
$A1 = md5("{$username}:{$auth_pieces['realm']}:{$password}");
$A2 = md5("{$method}:{$uri}");
$auth_pieces['response'] = md5("{$A1}:{$auth_pieces['nonce']}:{$nc}:{$cnonce}:{$auth_pieces['qop']}:${A2}");
$digest_header = "Digest username=\"{$username}\", realm=\"{$auth_pieces['realm']}\", nonce=\"{$auth_pieces['nonce']}\", uri=\"{$uri}\", cnonce=\"{$cnonce}\", nc={$nc}, qop=\"{$auth_pieces['qop']}\", response=\"{$auth_pieces['response']}\", opaque=\"{$auth_pieces['opaque']}\", algorithm=\"{$auth_pieces['algorithm']}\"";
$ht = new HttpRequest($url.$uri, HttpRequest::METH_GET);
$ht->setHeaders(array('Authorization'=>$digest_header));
$ht->send();
print_r($ht->getRawRequestMessage());
print_r($ht->getRawResponseMessage());