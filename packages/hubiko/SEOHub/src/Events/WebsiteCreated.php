<?php

namespace Hubiko\SEOHub\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Hubiko\SEOHub\Entities\SEOWebsite;

class WebsiteCreated
{
    use Dispatchable, SerializesModels;

    public $website;

    /**
     * Create a new event instance.
     */
    public function __construct(SEOWebsite $website)
    {
        $this->website = $website;
    }
}
