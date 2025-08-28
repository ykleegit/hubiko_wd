<?php

namespace Hubiko\EcommerceHub\Services;

use Hubiko\EcommerceHub\Entities\EcommerceOrder;
use Hubiko\EcommerceHub\Services\Gateways\StripeGateway;
use Hubiko\EcommerceHub\Services\Gateways\PayPalGateway;
use Hubiko\EcommerceHub\Services\Gateways\RazorpayGateway;
use Exception;

class PaymentGatewayService
{
    protected $gateways = [
        'stripe' => StripeGateway::class,
        'paypal' => PayPalGateway::class,
        'razorpay' => RazorpayGateway::class,
    ];

    public function getGateway($gateway)
    {
        if (!isset($this->gateways[$gateway])) {
            throw new Exception("Payment gateway '{$gateway}' not supported");
        }

        return new $this->gateways[$gateway]();
    }

    public function processPayment($gateway, $orderData, $paymentData)
    {
        $gatewayInstance = $this->getGateway($gateway);
        
        try {
            $result = $gatewayInstance->processPayment($orderData, $paymentData);
            
            if ($result['status'] === 'success') {
                $this->handleSuccessfulPayment($orderData['order_id'], $result);
            }
            
            return $result;
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    protected function handleSuccessfulPayment($orderId, $paymentResult)
    {
        $order = EcommerceOrder::find($orderId);
        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'payment_reference' => $paymentResult['transaction_id'] ?? null,
                'payment_details' => json_encode($paymentResult)
            ]);

            // Trigger order paid event for ecosystem integration
            event(new \Hubiko\EcommerceHub\Events\OrderPaid($order));
        }
    }

    public function getAvailableGateways($storeId)
    {
        // Get store-specific gateway configurations
        $store = \Hubiko\EcommerceHub\Entities\EcommerceStore::find($storeId);
        $settings = $store->settings ?? [];
        
        $available = [];
        foreach ($this->gateways as $key => $class) {
            if (isset($settings['payment_gateways'][$key]['enabled']) && 
                $settings['payment_gateways'][$key]['enabled']) {
                $available[$key] = [
                    'name' => ucfirst($key),
                    'class' => $class
                ];
            }
        }
        
        return $available;
    }
}
