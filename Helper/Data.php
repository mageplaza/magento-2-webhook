<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Helper;


use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Liquid\Template;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Mageplaza\Webhook\Model\Config\Source\Authentication;

/**
 * Class Data
 * @package Mageplaza\Blog\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mp_webhook';

    protected $liquidFilters;
    protected $curlFactory;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CurlFactory $curlFactory,
        LiquidFilters $liquidFilters
    )
    {
        parent::__construct($context, $objectManager, $storeManager);

        $this->liquidFilters = $liquidFilters;
        $this->curlFactory = $curlFactory;
    }

    public function sendHttpRequestFromHook($hook, $item)
    {
        $url = $this->generateLiquidTemplate($item,$hook->getPayloadUrl());
        $authentication = $hook->getAuthentication();
        $method = $hook->getMethod();
        $username = $hook->getUsername();
        $password = $hook->getPassword();
        if($authentication === Authentication::BASIC){
            $authentication = $this->getBasicAuthHeader($username,$password);
        }elseif ($authentication === Authentication::DIGEST){
            $authentication = $this->getDigestAuthHeader(
                $url,
                $method,
                $username,
                $hook->getRealm(),
                $password,
                $hook->getNonce(),
                $hook->getAlgorithm(),
                $hook->getQop(),
                $hook->getNonceCount(),
                $hook->getClientNonce(),
                $hook->getOpaque());
        }
        $body = $this->generateLiquidTemplate($item,$hook->getBody());
        $headers = $hook->getHeaders();
        $contentType = $hook->getContentType();
        return $this->sendHttpRequest($headers,$authentication,$contentType,$url,$body,$method);

    }

    public function generateLiquidTemplate($item, $templateHtml)
    {
        $template = new Template;
        $filtersMethods = $this->liquidFilters->getFiltersMethods();

        $template->registerFilter($this->liquidFilters);

        $template->parse($templateHtml, $filtersMethods);
        $content = $template->render([
            'item' => $item,
        ]);

        return $content;
    }

    public function sendHttpRequest($headers, $authentication, $contentType, $url, $body, $method)
    {
        if (!$method) {
            $method = 'GET';
        }
        if ($headers && !is_array($headers)) {
            $headers = $this::jsonDecode($headers);
        }
        $headersConfig = [];

        foreach ($headers as $header) {
            $key = $header['name'];
            $value = $header['value'];
            $headersConfig[] = trim($key) .': ' . trim($value);
        }

        if ($authentication) {
            $headersConfig[] = 'Authorization: ' . $authentication;
        }

        if ($contentType) {
            $headersConfig[] = 'Content-Type: '. $contentType;
        }
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('abc');

        $curl = $this->curlFactory->create();
        $curl->write($method, $url, '1.1', $headersConfig, $body);

        $result = ['success' => false];

        try {
            $resultCurl = $curl->read();
            $logger->info($resultCurl);
            $result['response'] = $resultCurl;
            if (!empty($resultCurl)) {
                $result['status'] = \Zend_Http_Response::extractCode($resultCurl);
                if (isset($result['status']) && in_array($result['status'], [200, 201])) {
                    $result['success'] = true;
                } else {
                    $result['message'] = __('Cannot connect to server. Please try again later.');
                }
            } else {
                $result['message'] = __('Cannot connect to server. Please try again later.');
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }

//        $curl->close();

        return $result;
    }

    public function getDigestAuthHeader($url, $method, $username, $realm, $password, $nonce, $algorithm, $qop, $nonceCount, $clientNonce, $opaque)
    {
        $uri = parse_url($url)[2];
        $method = $method ?: 'GET';
        $A1 = md5("{$username}:{$realm}:{$password}");
        $A2 = md5("{$method}:{$uri}");
        $response = md5("{$A1}:{$nonce}:{$nonceCount}:{$clientNonce}:{$qop}:${A2}");
        $digestHeader = "Digest username=\"{$username}\", realm=\"{$realm}\", nonce=\"{$nonce}\", uri=\"{$uri}\", cnonce=\"{$clientNonce}\", nc={$nonceCount}, qop=\"{$qop}\", response=\"{$response}\", opaque=\"{$opaque}\", algorithm=\"{$algorithm}\"";
        return $digestHeader;
    }

    public function getBasicAuthHeader($username,$password)
    {
        return 'Basic '. base64_encode("{$username}:{$password}");
    }
}
