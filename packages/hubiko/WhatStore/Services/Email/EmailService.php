<?php

namespace Hubiko\WhatStore\Services\Email;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Hubiko\WhatStore\Entities\EmailTemplate;
use Hubiko\WhatStore\Entities\EmailTemplateLang;
use Hubiko\WhatStore\Services\Email\Mailable\CommonEmailTemplate;

class EmailService
{
    /**
     * Send email based on template.
     *
     * @param string $templateName
     * @param string $to
     * @param array $data
     * @param object|null $store
     * @param string|null $orderId
     * @return array
     */
    public function sendEmailTemplate($templateName, $to, array $data, $store = null, $orderId = null)
    {
        $emailTemplate = EmailTemplate::where('name', $templateName)->first();
        
        if ($emailTemplate) {
            // Check if the mail settings are configured
            if ($this->isMailConfigured($store)) {
                // Get email content in the appropriate language
                $lang = $store ? $store->lang : config('app.locale');
                $content = EmailTemplateLang::where('parent_id', $emailTemplate->id)
                    ->where('lang', $lang)
                    ->first();
                
                if (!$content) {
                    // Fallback to default language
                    $content = EmailTemplateLang::where('parent_id', $emailTemplate->id)
                        ->where('lang', 'en')
                        ->first();
                }
                
                if ($content && !empty($content->content)) {
                    $content->from = $emailTemplate->from;
                    
                    // Replace variables in the content
                    $content->content = $this->replaceVariables($content->content, $data, $store, $orderId);
                    
                    try {
                        // Configure mail settings
                        $this->configureMailSettings($store);
                        
                        // Send the email
                        Mail::to($to)->send(new CommonEmailTemplate($content, $store));
                        
                        return [
                            'is_success' => true,
                            'error' => false,
                        ];
                    } catch (Exception $e) {
                        Log::error('Email sending error', [
                            'to' => $to,
                            'template' => $templateName,
                            'error' => $e->getMessage(),
                        ]);
                        
                        return [
                            'is_success' => false,
                            'error' => __('E-Mail has not been sent due to SMTP configuration: ' . $e->getMessage()),
                        ];
                    }
                } else {
                    return [
                        'is_success' => false,
                        'error' => __('Mail not sent, email content is empty'),
                    ];
                }
            } else {
                // Mail settings not configured, but don't show error
                return [
                    'is_success' => true,
                    'error' => false,
                ];
            }
        } else {
            return [
                'is_success' => false,
                'error' => __('Mail not sent, email template not found'),
            ];
        }
    }
    
    /**
     * Replace variables in the email content.
     *
     * @param string $content
     * @param array $data
     * @param object|null $store
     * @param string|null $orderId
     * @return string
     */
    protected function replaceVariables($content, array $data, $store = null, $orderId = null)
    {
        $variables = [
            '{store_name}' => $store ? $store->name : config('app.name'),
            '{order_no}' => $orderId ?? '',
            '{app_name}' => config('app.name'),
            '{app_url}' => '<a href="' . config('app.url') . '" target="_blank">' . config('app.url') . '</a>',
        ];
        
        // Add user provided data
        foreach ($data as $key => $value) {
            $variables['{' . $key . '}'] = $value;
        }
        
        // Replace all variables in the content
        return str_replace(array_keys($variables), array_values($variables), $content);
    }
    
    /**
     * Check if mail is configured.
     *
     * @param object|null $store
     * @return bool
     */
    protected function isMailConfigured($store)
    {
        if ($store && !empty($store->mail_driver)) {
            return true;
        }
        
        $settings = $this->getAdminMailSettings();
        
        return !empty($settings['mail_driver']);
    }
    
    /**
     * Configure mail settings.
     *
     * @param object|null $store
     * @return void
     */
    protected function configureMailSettings($store)
    {
        if ($store && !empty($store->mail_driver)) {
            // Use store-specific mail configuration
            config([
                'mail.driver' => $store->mail_driver,
                'mail.host' => $store->mail_host,
                'mail.port' => $store->mail_port,
                'mail.encryption' => $store->mail_encryption,
                'mail.username' => $store->mail_username,
                'mail.password' => $store->mail_password,
                'mail.from.address' => $store->mail_from_address,
                'mail.from.name' => $store->mail_from_name,
            ]);
        } else {
            // Use admin mail configuration
            $settings = $this->getAdminMailSettings();
            
            config([
                'mail.driver' => $settings['mail_driver'] ?? '',
                'mail.host' => $settings['mail_host'] ?? '',
                'mail.port' => $settings['mail_port'] ?? '',
                'mail.encryption' => $settings['mail_encryption'] ?? '',
                'mail.username' => $settings['mail_username'] ?? '',
                'mail.password' => $settings['mail_password'] ?? '',
                'mail.from.address' => $settings['mail_from_address'] ?? '',
                'mail.from.name' => $settings['mail_from_name'] ?? '',
            ]);
        }
    }
    
    /**
     * Get admin mail settings.
     *
     * @return array
     */
    protected function getAdminMailSettings()
    {
        // This should be replaced with your actual settings retrieval logic
        if (function_exists('Utility')) {
            return Utility::getAdminPaymentSetting();
        }
        
        return [];
    }
} 