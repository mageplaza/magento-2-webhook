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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Helper;

use Liquid\Template;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Model\Config\Source\Authentication;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;

/**
 * Class Data
 * @package Mageplaza\Webhook\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mp_webhook';

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $backendUrl
     * @param TransportBuilder $transportBuilder
     * @param CurlFactory $curlFactory
     * @param LiquidFilters $liquidFilters
     * @param HookFactory $hookFactory
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        UrlInterface $backendUrl,
        TransportBuilder $transportBuilder,
        CurlFactory $curlFactory,
        LiquidFilters $liquidFilters,
        HookFactory $hookFactory,
        HistoryFactory $historyFactory
    )
    {
        parent::__construct($context, $objectManager, $storeManager);

        $this->liquidFilters    = $liquidFilters;
        $this->curlFactory      = $curlFactory;
        $this->hookFactory      = $hookFactory;
        $this->historyFactory   = $historyFactory;
        $this->transportBuilder = $transportBuilder;
        $this->backendUrl       = $backendUrl;
    }

    /**
     * @param $hook
     * @param bool $item
     * @param bool $log
     * @return array
     */
    public function sendHttpRequestFromHook($hook, $item = false, $log = false)
    {
        $url            = $log ? $log->getPayloadUrl() : $this->generateLiquidTemplate($item, $hook->getPayloadUrl());
        $authentication = $hook->getAuthentication();
        $method         = $hook->getMethod();
        $username       = $hook->getUsername();
        $password       = $hook->getPassword();
        if ($authentication === Authentication::BASIC) {
            $authentication = $this->getBasicAuthHeader($username, $password);
        } else if ($authentication === Authentication::DIGEST) {
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
        $body        = $log ? $log->getBody() : $this->generateLiquidTemplate($item, $hook->getBody());
        $headers     = $hook->getHeaders();
        $contentType = $hook->getContentType();

        return $this->sendHttpRequest($headers, $authentication, $contentType, $url, $body, $method);
    }

    /**
     * @param $item
     * @param $templateHtml
     * @return string
     */
    public function generateLiquidTemplate($item, $templateHtml)
    {
        try {
            $template       = new Template;
            $filtersMethods = $this->liquidFilters->getFiltersMethods();

            $template->registerFilter($this->liquidFilters);

            $template->parse($templateHtml, $filtersMethods);
            $content = $template->render([
                'item' => $item,
            ]);

            return $content;
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return '';
    }

    /**
     * @param $headers
     * @param $authentication
     * @param $contentType
     * @param $url
     * @param $body
     * @param $method
     * @return array
     */
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
            $key             = $header['name'];
            $value           = $header['value'];
            $headersConfig[] = trim($key) . ': ' . trim($value);
        }

        if ($authentication) {
            $headersConfig[] = 'Authorization: ' . $authentication;
        }

        if ($contentType) {
            $headersConfig[] = 'Content-Type: ' . $contentType;
        }

        $curl = $this->curlFactory->create();
        $curl->write($method, $url, '1.1', $headersConfig, $body);

        $result = ['success' => false];

        try {
            $resultCurl         = $curl->read();
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
        $curl->close();

        return $result;
    }

    /**
     * @param $url
     * @param $method
     * @param $username
     * @param $realm
     * @param $password
     * @param $nonce
     * @param $algorithm
     * @param $qop
     * @param $nonceCount
     * @param $clientNonce
     * @param $opaque
     * @return string
     */
    public function getDigestAuthHeader($url, $method, $username, $realm, $password, $nonce, $algorithm, $qop, $nonceCount, $clientNonce, $opaque)
    {
        $uri          = parse_url($url)[2];
        $method       = $method ?: 'GET';
        $A1           = md5("{$username}:{$realm}:{$password}");
        $A2           = md5("{$method}:{$uri}");
        $response     = md5("{$A1}:{$nonce}:{$nonceCount}:{$clientNonce}:{$qop}:${A2}");
        $digestHeader = "Digest username=\"{$username}\", realm=\"{$realm}\", nonce=\"{$nonce}\", uri=\"{$uri}\", cnonce=\"{$clientNonce}\", nc={$nonceCount}, qop=\"{$qop}\", response=\"{$response}\", opaque=\"{$opaque}\", algorithm=\"{$algorithm}\"";

        return $digestHeader;
    }

    /**
     * @param $username
     * @param $password
     * @return string
     */
    public function getBasicAuthHeader($username, $password)
    {
        return 'Basic ' . base64_encode("{$username}:{$password}");
    }

    /**
     * @param $item
     * @param $hookType
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendObserver($item, $hookType)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $hookCollection = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', $hookType)
            ->addFieldToFilter('status', 1)
            ->setOrder('priority', 'ASC');

        $isSendMail = $this->getConfigGeneral('alert_enabled');
        $sendTo     = explode(',', $this->getConfigGeneral('send_to'));

        foreach ($hookCollection as $hook) {
            try {
                $history = $this->historyFactory->create();
                $data    = [
                    'hook_id'     => $hook->getId(),
                    'hook_name'   => $hook->getName(),
                    'store_ids'   => $hook->getStoreIds(),
                    'hook_type'   => $hook->getHookType(),
                    'priority'    => $hook->getPriority(),
                    'payload_url' => $this->generateLiquidTemplate($item, $hook->getPayloadUrl()),
                    'body'        => $this->generateLiquidTemplate($item, $hook->getBody())
                ];
                $history->addData($data);
                try {
                    $result = $this->sendHttpRequestFromHook($hook, $item);
                    $history->setResponse(isset($result['response']) ? $result['response'] : '');
                } catch (\Exception $e) {
                    $result = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
                if ($result['success'] == true) {
                    $history->setStatus(1);
                } else {
                    $history->setStatus(0)->setMessage($result['message']);
                    if ($isSendMail) {
                        $this->sendMail($sendTo,
                            __('Something went wrong while sending %1 hook', $hook->getName()),
                            $this->getConfigGeneral('email_template'),
                            $this->storeManager->getStore()->getId()
                        );
                    }
                }
                $history->save();
            } catch (\Exception $e) {
                if ($isSendMail) {
                    $this->sendMail($sendTo,
                        __('Something went wrong while sending %1 hook', $hook->getName()),
                        $this->getConfigGeneral('email_template'),
                        $this->storeManager->getStore()->getId());
                }
            }
        }
    }

    /**
     * @param $sendTo
     * @param $mes
     * @param $emailTemplate
     * @param $storeId
     *
     * @return bool
     */
    public function sendMail($sendTo, $mes, $emailTemplate, $storeId)
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'viewLogUrl' => $this->backendUrl->getUrl('mpwebhook/logs/'),
                    'mes'        => $mes
                ])
                ->setFrom('general')
                ->addTo($sendTo);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            return true;
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e->getLogMessage());
        }

        return false;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}