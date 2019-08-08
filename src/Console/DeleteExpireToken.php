<?php

namespace Fengxin2017\Oauth\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpireToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jkb:clear {--tag=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除数据库过期token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('tag') == 'de') {
            config('jkb.oauth_model')::where('expired_at', '<', Carbon::now())->delete();
            $this->info("clear successfully.");
        }
        $this->error('tag ' . $this->option . ' is invalid!');
    }
}