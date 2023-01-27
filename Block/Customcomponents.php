<?php
namespace NoFraud\Checkout\Block;

class Customcomponents extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    protected $tokenFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->tokenFactory = $tokenFactory;
        parent::__construct($context, $data);
    }

    /**
     * Process Customer session and return token.
     *
     * @return  string $tokenKey
     */
    public function getCustomerToken()
    {
        $tokenKey = "";
        try{
            if($this->customerSession->isLoggedIn()) {
                $customerId     = $this->customerSession->getCustomer()->getId();
                $customerToken  = $this->tokenFactory->create();
                $tokenKey       = $customerToken->createCustomerToken($customerId)->getToken();
            }
            return $tokenKey;
        }catch (\Exception $e){

        }
        return $tokenKey;
    }
}