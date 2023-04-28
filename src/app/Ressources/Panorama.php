<?php
namespace Citadelle\Stampyt\app\Ressources;


class Panorama extends Ressource
{

    public function get(string $reference, string $marketplace): array
    {
        $response = $this->api->get('panorama-shootings', ['ref' => self::reference($reference, $marketplace)]);

        foreach ($response as $panorama) {
            if ($panorama['type'] == 'STANDARD') {
                return $panorama['stampeds'];
            }
        }

        return [];
    }

    public function exist(string $reference, string $marketplace): bool
    {
        $response = $this->api->get('panorama-shootings/exists', ['ref' => self::reference($reference, $marketplace)]);
        return $this->hasPanorama($response);
    }

    public function exists(array $references, string $marketplace): array
    {
        $refs = [];
        foreach ($references as $reference) {
            $refs[$reference] = self::reference($reference, $marketplace);
        }

        $response = $this->api->get('panorama-shootings/exists', ['ref' => implode(',', $refs)]);

        $refs_exists = [];
        foreach ($response as $reference) {
            if ($this->hasPanorama($reference['types'])) {
                $ref = array_search($reference['ref'], $refs);
                if ($ref) {
                    $refs_exists[] = $ref;
                }
            }
        }

        return $refs_exists;
    }



    static function reference(string $reference, string $marketplace): string
    {
        return hash("sha512",  config('stampyt.userId')."_".$reference."_".$marketplace);
    }

    protected function hasPanorama(array $types): bool
    {
        return in_array('STANDARD', $types);
    }


}
