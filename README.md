# eCommerce Payments Extension for Bolt



## Configuring Providers

In the extension config you need to configure available providers you wish to 
use.

Each of these should be under the `providers:` key.

```twig
providers:
    2checkout:
        default:
            accountNumber: null
            secretWord: null
            testMode: true
    authorizenet:
        default:
            apiLoginId: null
            transactionKey: null
            liveEndpoint: 'https://secure.authorize.net/gateway/transact.dll'
            developerEndpoint: 'https://test.authorize.net/gateway/transact.dll'
            developerMode: false
            testMode: false
        sim:
            hashSecret: null
        dpm:
            hashSecret: null
    buckaroo
        default:
            websiteKey: null
            secretKey: null
            testMode: true
    cardsave:
        default:
            merchantId: null
            password: null
    coinbase:
        default:
            apiKey: null
            secret: null
            accountId: null
    eway:
        default:
            apiKey: null
            password: null
            testMode: true
        direct:
            customerId: null
    firstdata:
        default:
            testMode: false
        connect:
            storeId: null
            sharedSecret: null
        global:
            gatewayid: null
            password: null
        payeezy:
            gatewayid: null
            password: null
        webservice:
            sslCertificate: null
            sslKey: null
            sslKeyPassword: null
            userName: null
            password: null
    gocardless:
        default:
            appId: null
            appSecret: null
            merchantId: null
            accessToken: null
            testMode: true
    migs
        default:
            merchantId: null
            merchantAccessCode: null
            secureHash: null
    mollie:
        default:
            apiKey: null
    multisafepay:
        default:
            testMode: true
        rest:
            apiKey: null
            locale: en
        xml:
            accountId: null
            siteId: null
            siteCode: null
    netaxept:
        default:
            merchantId: null
            password: null
            testMode: true
    netbanx:
       default:
            accountNumber: null
            storeId: null
            storePassword: null
            testMode: true
    payfast:
        default:
            merchantId: null
            merchantKey: null
            pdtKey: null
            testMode: true
    payflow:
        default:
            username: null
            password: null
            vendor: null
            partner: null
            testMode: true
    paymentexpress:
        default:
            username: null
            password: null
    paypal:
        default:
            testMode: true
        express:
            username: null
            password: null
            signature: null
            solutionType' => ['Sole', 'Mark']
            landingPage' => ['Billing', 'Login']
            brandName: null
            headerImageUrl: null
            logoImageUrl: null
            borderColor: null
        pro:
            username: null
            password: null
            signature: null
        rest:
            clientId: null
            secret: null
            token: null
    pin:
        default:
            secretKey: null
            testMode: true
    sagepay:
        default:
            vendor: null
            testMode: true
            referrerId: null
            testMode: true
    securepay
        default:
            merchantId: null
            transactionPassword: null
            testMode: true
    stripe:
        default:
            apiKey: null
    targetpay:
        default:
            subAccountId: null
    worldpay:
        default:
            installationId: null
            accountId: null
            secretWord: null
            callbackPassword: null
            testMode: true
```
