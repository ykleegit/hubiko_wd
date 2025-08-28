<?php

namespace Hubiko\SEOHub\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Hubiko\SEOHub\Entities\SEOAudit;

class AuditCompleted
{
    use Dispatchable, SerializesModels;

    public $audit;

    /**
     * Create a new event instance.
     */
    public function __construct(SEOAudit $audit)
    {
        $this->audit = $audit;
    }
}
