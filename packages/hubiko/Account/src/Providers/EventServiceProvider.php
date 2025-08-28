<?php

namespace Hubiko\Account\Providers;

use App\Events\BankTransferPaymentStatus;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\CreatePaymentInvoice;
use App\Events\CreatePaymentPurchase;
use App\Events\DefaultData;
use App\Events\DestroyInvoice;
use App\Events\DestroyPurchase;
use App\Events\GivePermissionToRole;
use App\Events\PaymentDestroyInvoice;
use App\Events\PaymentDestroyPurchase;
use App\Events\ProductDestroyInvoice;
use App\Events\SentInvoice;
use App\Events\SentPurchase;
use App\Events\UpdateInvoice;
use App\Events\UpdatePurchase;
use Hubiko\Account\Listeners\CompanyMenuListener;
use Hubiko\Account\Listeners\CompanySettingListener;
use Hubiko\Account\Listeners\CompanySettingMenuListener;
use Hubiko\Account\Listeners\InvoiceBalanceTransfer;
use Hubiko\AamarPay\Events\AamarPaymentStatus;
use Hubiko\Account\Events\CreateCustomerCreditNote;
use Hubiko\Account\Events\CreateCustomerDebitNote;
use Hubiko\Account\Events\CreatePayment;
use Hubiko\Account\Events\CreatePaymentBill;
use Hubiko\Account\Events\CreateRevenue;
use Hubiko\Account\Events\DestroyBill;
use Hubiko\Account\Events\DestroyCustomerCreditNote;
use Hubiko\Account\Events\DestroyCustomerDebitNote;
use Hubiko\Account\Events\DestroyPayment;
use Hubiko\Account\Events\DestroyPurchaseProduct;
use Hubiko\Account\Events\DestroyRevenue;
use Hubiko\Account\Events\PaymentDestroyBill;
use Hubiko\Account\Events\ProductDestroyBill;
use Hubiko\Account\Events\SentBill;
use Hubiko\Account\Events\UpdateBill;
use Hubiko\Account\Events\UpdateCustomerCreditNote;
use Hubiko\Account\Events\UpdateCustomerDebitNote;
use Hubiko\Account\Events\UpdatePayment;
use Hubiko\Account\Events\UpdateRevenue;
use Hubiko\Account\Listeners\BillDestroy;
use Hubiko\Account\Listeners\BillPaymentCreate;
use Hubiko\Account\Listeners\BillPaymentDestroy;
use Hubiko\Account\Listeners\BillProductDestroy;
use Hubiko\Account\Listeners\BillSent;
use Hubiko\Account\Listeners\BillUpdate;
use Hubiko\Account\Listeners\CreateProductLis;
use Hubiko\Account\Listeners\CustomerCreditNoteCreate;
use Hubiko\Account\Listeners\CustomerCreditNoteDestroy;
use Hubiko\Account\Listeners\CustomerCreditNoteUpdate;
use Hubiko\Account\Listeners\CustomerDebitNoteCreate;
use Hubiko\Account\Listeners\CustomerDebitNoteDestroy;
use Hubiko\Account\Listeners\CustomerDebitNoteUpdate;
use Hubiko\Account\Listeners\DataDefault;
use Hubiko\Account\Listeners\GiveRoleToPermission;
use Hubiko\Account\Listeners\InvoiceDestroy;
use Hubiko\Account\Listeners\InvoiceOnlinePayamentCreate;
use Hubiko\Account\Listeners\InvoicePaymentCreate;
use Hubiko\Account\Listeners\InvoicePaymentDestroy;
use Hubiko\Account\Listeners\InvoiceProductDestroy;
use Hubiko\Account\Listeners\InvoiceSent;
use Hubiko\Account\Listeners\InvoiceUpdate;
use Hubiko\Account\Listeners\PaymentCreate;
use Hubiko\Account\Listeners\PaymentDestroy;
use Hubiko\Account\Listeners\PaymentUpdate;
use Hubiko\Account\Listeners\PurchaseDestroy;
use Hubiko\Account\Listeners\PurchasePaymentCreate;
use Hubiko\Account\Listeners\PurchasePaymentDestroy;
use Hubiko\Account\Listeners\PurchaseProductDestroy;
use Hubiko\Account\Listeners\PurchaseSent;
use Hubiko\Account\Listeners\PurchaseUpdate;
use Hubiko\Account\Listeners\RetainerPaymentCreate;
use Hubiko\Account\Listeners\RevenueCreate;
use Hubiko\Account\Listeners\RevenueDestroy;
use Hubiko\Account\Listeners\RevenueUpdate;
use Hubiko\Account\Listeners\UpdateProductLis;
use Hubiko\AuthorizeNet\Events\AuthorizeNetStatus;
use Hubiko\Benefit\Events\BenefitPaymentStatus;
use Hubiko\BlueSnap\Events\BlueSnapPaymentStatus;
use Hubiko\Braintree\Events\BraintreePaymentStatus;
use Hubiko\BTCPay\Events\BTCPayPaymentStatus;
use Hubiko\Cashfree\Events\CashfreePaymentStatus;
use Hubiko\Checkout\Events\CheckoutPaymentStatus;
use Hubiko\CinetPay\Events\CinetPayPaymentStatus;
use Hubiko\Coin\Events\CoinPaymentStatus;
use Hubiko\Coingate\Events\CoingatePaymentStatus;
use Hubiko\CyberSource\Events\CybersourceStatus;
use Hubiko\DPOPay\Events\DPOPayPaymentStatus;
use Hubiko\Easebuzz\Events\EasebuzzPaymentStatus;
use Hubiko\Esewa\Events\EsewaPaymentStatus;
use Hubiko\Fatora\Events\FatoraPaymentStatus;
use Hubiko\Fedapay\Events\FedapayPaymentStatus;
use Hubiko\Flutterwave\Events\FlutterwavePaymentStatus;
use Hubiko\Instamojo\Events\InstamojoPaymentStatus;
use Hubiko\Iyzipay\Events\IyzipayPaymentStatus;
use Hubiko\Khalti\Events\KhaltiPaymentStatus;
use Hubiko\LinePay\Events\LinePayPaymentStatus;
use Hubiko\Mercado\Events\MercadoPaymentStatus;
use Hubiko\Midtrans\Events\MidtransPaymentStatus;
use Hubiko\Mollie\Events\MolliePaymentStatus;
use Hubiko\Moneris\Events\MonerisPaymentStatus;
use Hubiko\Monnify\Events\MonnifyPaymentStatus;
use Hubiko\Moyasar\Events\MoyasarPaymentStatus;
use Hubiko\MyFatoorah\Events\MyFatoorahStatus;
use Hubiko\Nepalste\Events\NepalstePaymentStatus;
use Hubiko\NMI\Events\NMIPatmentStats;
use Hubiko\Ozow\Events\OzowPaymentStatus;
use Hubiko\Paddle\Events\PaddlePaymentStatus;
use Hubiko\PaiementPro\Events\PaiementProPaymentStatus;
use Hubiko\Payfast\Events\PayfastPaymentStatus;
use Hubiko\PayFort\Events\PayfortPaymentStatus;
use Hubiko\PayHere\Events\PayHerePaymentStatus;
use Hubiko\Paynow\Events\PaynowPaymentStatus;
use Hubiko\Paypal\Events\PaypalPaymentStatus;
use Hubiko\Paystack\Events\PaystackPaymentStatus;
use Hubiko\PayTab\Events\PaytabPaymentStatus;
use Hubiko\Paytm\Events\PaytmPaymentStatus;
use Hubiko\PayTR\Events\PaytrPaymentStatus;
use Hubiko\PhonePe\Events\PhonePePaymentStatus;
use Hubiko\ProductService\Events\CreateProduct;
use Hubiko\ProductService\Events\UpdateProduct;
use Hubiko\Razorpay\Events\RazorpayPaymentStatus;
use Hubiko\Skrill\Events\SkrillPaymentStatus;
use Hubiko\SSPay\Events\SSpayPaymentStatus;
use Hubiko\Stripe\Events\StripePaymentStatus;
use Hubiko\Tap\Events\TapPaymentStatus;
use Hubiko\Toyyibpay\Events\ToyyibpayPaymentStatus;
use Hubiko\Xendit\Events\XenditPaymentStatus;
use Hubiko\YooKassa\Events\YooKassaPaymentStatus;
use Hubiko\Paypay\Events\PaypayPaymentStatus;
use Hubiko\PayU\Events\PayUPaymentStatus;
use Hubiko\PeachPayment\Events\PeachPaymentStatus;
use Hubiko\Pesapal\Events\PesapalPaymentStatus;
use Hubiko\PowerTranz\Events\PowerTranzPaymentStatus;
use Hubiko\Retainer\Events\RetainerConvertToInvoice;
use Hubiko\SenangPay\Events\SenangPayPaymentStatus;
use Hubiko\Sofort\Events\SofortPaymentStatus;
use Hubiko\Square\Events\SquarePaymentStatus;
use Hubiko\SSLCommerz\Events\SSLCommerzPaymentStatus;
use Hubiko\TwoCheckout\Events\TwoCheckoutPaymentStatus;
use Hubiko\UddoktaPay\Events\UddoktaPayStatus;
use Hubiko\Yoco\Events\YocoPaymentStatus;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        DefaultData :: class => [
            DataDefault::class,
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],

        StripePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaypalPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        FlutterwavePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaystackPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        RazorpayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MolliePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayfastPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        YooKassaPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaytabPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SSpayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        ToyyibpayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SkrillPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        IyzipayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaytrPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        AamarPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        BenefitPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CashfreePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CoingatePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MercadoPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaytmPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaddlePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MidtransPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        XenditPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        TapPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        KhaltiPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PhonePePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        AuthorizeNetStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayHerePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaiementProPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        FedapayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        BankTransferPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaypayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        DPOPayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SofortPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SSLCommerzPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaynowPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        SquarePaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PowerTranzPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MoyasarPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        BraintreePaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        InstamojoPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        SentInvoice::class =>[
            InvoiceSent::class
        ],
        CreatePaymentBill::class =>[
            BillPaymentCreate::class
        ],
        SentBill::class =>[
            BillSent::class
        ],
        UpdateBill::class =>[
            BillUpdate::class
        ],
        CreatePaymentInvoice::class =>[
            InvoicePaymentCreate::class
        ],
        UpdateInvoice::class =>[
            InvoiceUpdate::class
        ],
        CreateProduct::class =>[
            CreateProductLis::class
        ],
        UpdateProduct::class =>[
            UpdateProductLis::class
        ],
        PeachPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        OzowPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CybersourceStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SenangPayPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        NepalstePaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        TwoCheckoutPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        EasebuzzPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayUPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        FatoraPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        NMIPatmentStats::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MonerisPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CinetPayPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CheckoutPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        BlueSnapPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        BTCPayPaymentStatus::class => [
          InvoiceBalanceTransfer::class,
          InvoiceOnlinePayamentCreate::class
        ],
        PesapalPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        CoinPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        UddoktaPayStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MyFatoorahStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MonnifyPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        LinePayPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayfortPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        EsewaPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],  
        YocoPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaymentDestroyInvoice::class => [
            InvoicePaymentDestroy::class
        ],
        DestroyInvoice::class => [
            InvoiceDestroy::class
        ],
        ProductDestroyInvoice::class => [
            InvoiceProductDestroy::class
        ],
        CreateCustomerCreditNote::class => [
            CustomerCreditNoteCreate::class
        ],
        UpdateCustomerCreditNote::class => [
            CustomerCreditNoteUpdate::class
        ],
        DestroyCustomerCreditNote::class => [
            CustomerCreditNoteDestroy::class
        ],
        RetainerConvertToInvoice::class => [
            InvoiceSent::class,
            RetainerPaymentCreate::class
        ],
        ProductDestroyBill::class => [
            BillProductDestroy::class
        ],
        DestroyBill::class => [
            BillDestroy::class
        ],
        PaymentDestroyBill::class => [
            BillPaymentDestroy::class
        ],
        CreateCustomerDebitNote::class => [
            CustomerDebitNoteCreate::class
        ],
        UpdateCustomerDebitNote::class => [
            CustomerDebitNoteUpdate::class
        ],
        DestroyCustomerDebitNote::class => [
            CustomerDebitNoteDestroy::class
        ],
        SentPurchase::class => [
            PurchaseSent::class
        ],
        UpdatePurchase::class => [
            PurchaseUpdate::class
        ],
        DestroyPurchase::class => [
            PurchaseDestroy::class
        ],
        DestroyPurchaseProduct::class => [
            PurchaseProductDestroy::class
        ],
        CreatePaymentPurchase::class => [
            PurchasePaymentCreate::class
        ],
        PaymentDestroyPurchase::class => [
            PurchasePaymentDestroy::class
        ],
        CreateRevenue::class => [
            RevenueCreate::class
        ],
        DestroyRevenue::class => [
            RevenueDestroy::class
        ],
        UpdateRevenue::class => [
            RevenueUpdate::class
        ],
        CreatePayment::class => [
            PaymentCreate::class
        ],
        DestroyPayment::class => [
            PaymentDestroy::class
        ],
        UpdatePayment::class => [
            PaymentUpdate::class
        ]
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
