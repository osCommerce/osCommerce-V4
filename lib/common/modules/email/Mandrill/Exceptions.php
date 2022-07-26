<?php 

namespace common\modules\email\Mandrill;
use common\modules\email\Mandrill;
class Error extends Exception {}
class HttpError extends Error {}

/**
 * The parameters passed to the API call are invalid or not provided when required
 */
class ValidationError extends Error {}

/**
 * The provided API key is not a valid Mandrill API key
 */
class Invalid_Key extends Error {}

/**
 * The requested feature requires payment.
 */
class PaymentRequired extends Error {}

/**
 * The provided subaccount id does not exist.
 */
class Unknown_Subaccount extends Error {}

/**
 * The requested template does not exist
 */
class Unknown_Template extends Error {}

/**
 * The subsystem providing this API call is down for maintenance
 */
class ServiceUnavailable extends Error {}

/**
 * The provided message id does not exist.
 */
class Unknown_Message extends Error {}

/**
 * The requested tag does not exist or contains invalid characters
 */
class Invalid_Tag_Name extends Error {}

/**
 * The requested email is not in the rejection list
 */
class Invalid_Reject extends Error {}

/**
 * The requested sender does not exist
 */
class Unknown_Sender extends Error {}

/**
 * The requested URL has not been seen in a tracked link
 */
class Unknown_Url extends Error {}

/**
 * The provided tracking domain does not exist.
 */
class Unknown_TrackingDomain extends Error {}

/**
 * The given template name already exists or contains invalid characters
 */
class Invalid_Template extends Error {}

/**
 * The requested webhook does not exist
 */
class Unknown_Webhook extends Error {}

/**
 * The requested inbound domain does not exist
 */
class Unknown_InboundDomain extends Error {}

/**
 * The provided inbound route does not exist.
 */
class Unknown_InboundRoute extends Error {}

/**
 * The requested export job does not exist
 */
class Unknown_Export extends Error {}

/**
 * A dedicated IP cannot be provisioned while another request is pending.
 */
class IP_ProvisionLimit extends Error {}

/**
 * The provided dedicated IP pool does not exist.
 */
class Unknown_Pool extends Error {}

/**
 * The user hasn't started sending yet.
 */
class NoSendingHistory extends Error {}

/**
 * The user's reputation is too low to continue.
 */
class PoorReputation extends Error {}

/**
 * The provided dedicated IP does not exist.
 */
class Unknown_IP extends Error {}

/**
 * You cannot remove the last IP from your default IP pool.
 */
class Invalid_EmptyDefaultPool extends Error {}

/**
 * The default pool cannot be deleted.
 */
class Invalid_DeleteDefaultPool extends Error {}

/**
 * Non-empty pools cannot be deleted.
 */
class Invalid_DeleteNonEmptyPool extends Error {}

/**
 * The domain name is not configured for use as the dedicated IP's custom reverse DNS.
 */
class Invalid_CustomDNS extends Error {}

/**
 * A custom DNS change for this dedicated IP is currently pending.
 */
class Invalid_CustomDNSPending extends Error {}

/**
 * Custom metadata field limit reached.
 */
class Metadata_FieldLimit extends Error {}

/**
 * The provided metadata field name does not exist.
 */
class Unknown_MetadataField extends Error {}


