<?php

namespace Hubiko\SEOHub\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Hubiko\SEOHub\Entities\SEOIssue;

class IssueDetected
{
    use Dispatchable, SerializesModels;

    public $issue;

    /**
     * Create a new event instance.
     */
    public function __construct(SEOIssue $issue)
    {
        $this->issue = $issue;
    }
}
