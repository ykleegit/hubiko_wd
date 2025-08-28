<?php

namespace Hubiko\SEOHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Hubiko\SEOHub\Entities\SEOWebsite;
use Hubiko\SEOHub\Services\SEOAuditService;
use Illuminate\Support\Facades\Log;

class RunSEOAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $website;

    /**
     * Create a new job instance.
     */
    public function __construct(SEOWebsite $website)
    {
        $this->website = $website;
    }

    /**
     * Execute the job.
     */
    public function handle(SEOAuditService $auditService): void
    {
        try {
            $auditService->runAudit($this->website);
        } catch (\Exception $e) {
            Log::error('SEO Audit Job Failed', [
                'website_id' => $this->website->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
