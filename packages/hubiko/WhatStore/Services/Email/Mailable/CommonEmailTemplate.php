<?php

namespace Hubiko\WhatStore\Services\Email\Mailable;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The email template content.
     *
     * @var object
     */
    public $template;

    /**
     * The store context.
     *
     * @var object|null
     */
    public $store;

    /**
     * Create a new message instance.
     *
     * @param object $template
     * @param object|null $store
     * @return void
     */
    public function __construct($template, $store = null)
    {
        $this->template = $template;
        $this->store = $store;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromName = $this->store ? $this->store->name : config('app.name');
        $fromAddress = $this->store && !empty($this->store->mail_from_address) 
            ? $this->store->mail_from_address 
            : $this->template->from;

        return $this->from($fromAddress, $fromName)
            ->subject($this->template->subject)
            ->markdown('whatstore::emails.common')
            ->with([
                'content' => $this->template->content,
                'mail_header' => $fromName,
            ]);
    }
} 