<?php

namespace App\Jobs\ApparelMagic;

use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getApparelMagicCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ApparelMagicHelper;
    protected $page_size,$startAfter,$settings;
    /**
     * Create a new job instance.
     */
    public function __construct($page_size,$startAfter,$settings)
    {
        $this->page_size = $page_size;
        $this->startAfter = $startAfter;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->getAmCustomer($this->page_size,$this->startAfter,$this->settings);
    }
}
