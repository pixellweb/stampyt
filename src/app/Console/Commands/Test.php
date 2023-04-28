<?php

namespace Citadelle\Stampyt\app\Console\Commands;


use Citadelle\Stampyt\app\Ressources\Panorama;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stampyt:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test stampyt';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% -- %message%');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $panorama = new Panorama();

        dd($panorama->get('JMZKF6W7A00753251', 'CCIE_MARTINIQUE'));
        //dd($panorama->exist('JMZKF6W7A00753251', 'CCIE_MARTINIQUE'));

        //dd($panorama->exists(['JMZKF6W7A00753251', 'rrrr', 'WF0BXXMRKBHP37491'], 'CCIE_MARTINIQUE'));
    }



}
