<?php

namespace Citadelle\Stampyt\app\Console\Commands;


use Carbon\Carbon;
use Citadelle\Stampyt\app\Api;
use Citadelle\Stampyt\app\Document;
use Citadelle\Stampyt\app\Element;
use Citadelle\Stampyt\app\Ressources\Panorama;
use Citadelle\Stampyt\app\Vehicule;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Ipsum\Media\app\Models\Media;
use Symfony\Component\Console\Helper\ProgressBar;
use Illuminate\Support\Facades\Cache;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stampyt
                                {--no-cache : Tous les véhicules sont à nouveaux importé.}
                                {--debug : Show process output or not. Useful for debugging.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import des photos stampyt et du lecteur 360';


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
        $this->info(PHP_EOL . '**** Import stampyt ****');
        $progress_bar = $this->startProgressBar(0);
        $progress_bar->setMessage('Import des véhicules');

        $query = config('stampyt.model_vehicule.class')::whereNotNull(config('stampyt.model_vehicule.identifiant'));

        if (!$this->option('no-cache')) {
            $query->whereNull('stampyt_player');
        }

        $sources_vehicules = $query->get()->groupBy('source_id');


        $panorama = new Panorama();

        foreach ($sources_vehicules as $vehicules) {

            $marketplace = $vehicules[0]->source->stampyt_marketplace;

            $this->info('Recherche '.$marketplace);

            if (!$marketplace) {
                continue;
            }

            $per_page = 50;
            $nb_pages = ceil($vehicules->count() / $per_page);
            for ($page = 1; $page <= $nb_pages; $page++) {
                $vehicules_page = $vehicules->forPage($page, $per_page);

                $vehicules_with_panorama = $panorama->exists($vehicules_page->pluck('vin')->toArray(), $marketplace);

                foreach ($vehicules_with_panorama as $vin) {
                    $this->info('Véhicule '.$vin);

                    $vehicule = $vehicules_page->where('vin', $vin)->first();


                    if ($vehicule->pixmycar_player) {
                        // Suppression des medias pixmycar
                        $vehicule->pixmycar_player = null;
                        foreach ($vehicule->images->where('groupe', 'pixmycar') as $media) {
                            self::deleteMedia($media);
                        }
                    }


                    $photos = collect($panorama->get($vin, $marketplace));
                    $vehicule->stampyt_player = Panorama::reference($vin, $marketplace);
                    $vehicule->save();


                    $ids = [];
                    foreach ($photos as $photo) {
                        $fichier_nom = basename($photo['url']);

                        $media = $vehicule->images->where('fichier', $fichier_nom)->first();

                        if ($media) {
                            if (File::exists($media->path)) {
                                \Croppa::delete($media->cropPath);
                            }
                        } else {
                            // Enregistrement en bdd
                            $media = new Media;
                            $media->titre = $fichier_nom;
                            $media->fichier = $fichier_nom;
                            $media->type = Media::TYPE_IMAGE;
                            $media->repertoire = 'stampyt';
                            $media->publication_id = $vehicule->id;
                            $media->publication_type = config('stampyt.model_vehicule.class');
                            $media->groupe = 'stampyt';
                            $media->save();

                        }

                        $fichier = file_get_contents($photo['url']);
                        file_put_contents(public_path(config('ipsum.media.path').'stampyt/'.$fichier_nom), $fichier);

                        $ids[] = $fichier_nom;
                    }

                    // Suppression des medias qui n'existe plus
                    foreach ($vehicule->images->whereNotIn('fichier', $ids) as $media) {
                        self::deleteMedia($media);
                    }

                }

            }

        }


        $this->finishProgressBar($progress_bar);
        $this->info(PHP_EOL . '**** Fin import ****');
    }


    protected function startProgressBar($max_steps)
    {
        $progress_bar = $this->output->createProgressBar($max_steps);
        $progress_bar->setFormat('custom');
        $progress_bar->setMessage('Start');
        $progress_bar->start();

        return $progress_bar;
    }

    protected function finishProgressBar($progress_bar)
    {
        $progress_bar->setMessage('Finish');
        $progress_bar->finish();
    }


    static function deleteMedia(Media $media)
    {
        $media->delete();
        if (File::exists($media->path)) {
            \Croppa::delete($media->cropPath);
        }
    }

}
