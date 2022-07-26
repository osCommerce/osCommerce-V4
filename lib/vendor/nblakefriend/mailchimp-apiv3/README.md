# MailChimp API v3.0 PHP Wrapper

PHP wrapper for the MailChimp API v 3.0.

### Dependencies
- GuzzleHttp
- PHP > 5.4

*Project still in the works. More documentation to come*

### Installing
Using Composer: `composer require nblakefriend/mailchimp-apiv3`

### Getting Started
When downloading from composer *(recommended)*:
1. In `vendor/nblakefriend/mailchimp-apiv3/src` create `config.ini` file with structure:

```
[api_keys]
key1[api_keys] = "yourmcapikey-usx"
key1[active] = true
```

Multiple accounts can be configured in this config file.

```
[api_keys]
key1[api_keys] = "yourmcapikey-usx"
key1[active] = true

key2[api_keys] = "yourmcapikey-usx"
key2[active] = false
```

Whichever key[active] is true will be used.

***config.ini is excluded in the .gitignore file. Make sure this is not changed!***

2. Instantiate with `$mc = new MailChimp\MailChimp`;
3. `print_r($mc->getAccountInfo());` should return the MailChimp API Root call.

If downloading this repo directly:
1. From your command line, navigate to the MailChimp folder and run `composer update` to download Dependencies.
2. Add the package to your add `require_once 'MailChimp/vendor/autoload.php'` in your file.
3. Instantiate with `$mc = new MailChimp\MailChimp`;
4. `print_r($mc->getAccountInfo());` should return the MailChimp API Root call.

### Using the Wrapper
Each MailChimp collections *(lists, campaigns, e-commerce etc.)* is accessed using a method found at the bottom of the `MailChimp.php` file that instantiates the collection's class.

**For example:**
*Assuming your MailChimp instance is stored in the `$mc` variable*

#### Lists
`$mc->lists()->getLists();`

This would return the response from calling /lists
http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists

#### E-commerce
Adding a new store customer:

`$mc->ecommerce()->customers()->addCustomer("STORE123", "CUST123", "freddie@freddiesjokes.com", true);`

This would create a new customer to the store with id `STORE123` with the customer id `CUST123` and the email address `freddie@freddiesjokes.com` and an opt-in status of true which subscribes the customer to the list.

**Collection Reference**
* authorizedApps()
* automations()
* batchOps()
* campaignFolders()
* campaigns()
* conversations()
* ecommerce()
*   - ecommerce()->carts()
*   - ecommerce()->customers()
*   - ecommerce()->orders()
*   - ecommerce()->products()
* fileManager()
* lists()
* reports()
* templateFolders()
* templates()

**[See complete list of available methods for each class/collection here](https://nblakefriend.github.io/MailChimp-API3.0-Wrapper/index.html)**

Docs also able to be run locally from the `docs/index.html`
