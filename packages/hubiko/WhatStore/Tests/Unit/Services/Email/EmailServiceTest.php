<?php

namespace Hubiko\WhatStore\Tests\Unit\Services\Email;

use Illuminate\Support\Facades\Mail;
use Hubiko\WhatStore\Services\Email\EmailService;
use Hubiko\WhatStore\Services\Email\Mailable\CommonEmailTemplate;
use Hubiko\WhatStore\Tests\TestCase;

class EmailServiceTest extends TestCase
{
    protected $emailService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->emailService = new EmailService();
    }
    
    /** @test */
    public function it_sends_order_confirmation_email()
    {
        Mail::fake();
        
        $this->emailService->sendOrderConfirmation([
            'email' => 'customer@example.com',
            'order_id' => '12345',
            'order_total' => 99.99,
            'items' => [
                ['name' => 'Test Product', 'price' => 99.99]
            ]
        ]);
        
        Mail::assertSent(CommonEmailTemplate::class, function ($mail) {
            return $mail->hasTo('customer@example.com') &&
                   $mail->subject === 'Order Confirmation';
        });
    }
    
    /** @test */
    public function it_sends_payment_confirmation_email()
    {
        Mail::fake();
        
        $this->emailService->sendPaymentConfirmation([
            'email' => 'customer@example.com',
            'order_id' => '12345',
            'payment_amount' => 99.99,
            'payment_method' => 'Credit Card'
        ]);
        
        Mail::assertSent(CommonEmailTemplate::class, function ($mail) {
            return $mail->hasTo('customer@example.com') &&
                   $mail->subject === 'Payment Confirmation';
        });
    }
} 