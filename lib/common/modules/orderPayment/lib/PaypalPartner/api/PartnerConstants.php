<?php

namespace common\modules\orderPayment\lib\PaypalPartner\api;

/**
 * Class PartnerConstants
 * Placeholder for Paypal Constants
 *
 * @package PayPal\Core
 */
class PartnerConstants
{
    const NAME_TYPE = 'LEGAL';
    const INDIVIDUAL_TYPE = 'PRIMARY';

    const BUSINESS_TYPE = 'INDIVIDUAL';
    const BUSINESS_SUBTYPE = 'ASSO_TYPE_INCORPORATED';

    const ADDRESS_TYPE_HOME = 'HOME';
    const ADDRESS_TYPE_WORK = 'WORK';
    
    const OPERATION = 'API_INTEGRATION';
    
    const PRODUCT_EXPRESS_CHECKOUT = 'EXPRESS_CHECKOUT';
    const PRODUCT = 'PPCP';
    
    const CONSENTS_TYPE = 'SHARE_DATA_CONSENT';

    const APPROVAL_URL = 'approval_url';
    
    const CUSTOMER_TYPE = 'MERCHANT';
    
    
    const REFERRALUSER_TYPE = 'PAYER_ID';
    const PARTNERIDENTIFIER_TYPE = 'TRACKING_ID';
    
    
    
    const INTEGRATION_METHOD = 'PAYPAL';
    const INTEGRATION_TYPE_TP = 'THIRD_PARTY';
    const INTEGRATION_TYPE_FP = 'FIRST_PARTY';
    
    const FEATURE_PAYMENT = 'PAYMENT';
    const FEATURE_REFUND = 'REFUND';
    const FEATURE_FEE = 'PARTNER_FEE';
    const FEATURE_DELAY = 'DELAY_FUNDS_DISBURSEMENT';
    const FEATURE_INFO = 'ACCESS_MERCHANT_INFORMATION';
    
    
}
