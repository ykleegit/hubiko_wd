<?php

namespace Hubiko\WhatStore\Services\Webhook;

use Illuminate\Http\Request;

interface WebhookHandlerInterface
{
    /**
     * Handle an incoming webhook request.
     *
     * @param Request $request
     * @return array
     */
    public function handle(Request $request);
} 