<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Normalize botanical attributes across all catalog products
 * (ornamental shrubs, berry shrubs, and fruit trees) so every product card
 * and product detail page has consistent, comparable specifications.
 */
final class NormalizeCatalogProductAttributes extends AbstractMigration
{
    /**
     * Map of product slugs to their botanical attributes.
     *
     * @return array<string, array<string, array{name: string, value: string}>>
     */
    private function getProductAttributesMap(): array
    {
        return [
            // Ornamental Shrubs
            'cornus-alba-sibirica' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Lăstari roșii / Flori albe'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 250 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'lavanda-lavandula-angustifolia-hidcote' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Violet-închis'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '40 - 60 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iunie - August'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '0.4 x 0.4 m'],
            ],
            'azalea-japonica' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Roz-roșu intens'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '60 - 100 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Aprilie - Mai'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '0.8 x 0.8 m'],
            ],
            'magnolia-stellata' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb pur'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '200 - 300 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Martie - Aprilie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2.5 x 2.5 m'],
            ],
            'viburnum-opulus-roseum-bulgare-de-zapada' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb (inflorescențe sferice)'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '250 - 350 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2 x 2 m'],
            ],
            'deutzia-gracilis' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '80 - 120 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1 x 1 m'],
            ],
            'philadelphus-coronarius-iasomie' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb parfumat'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '200 - 300 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'liliac-syringa-vulgaris' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Violet parfumat'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '250 - 350 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Aprilie - Mai'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2 x 2 m'],
            ],
            'hibiscus-syriacus-zamosita' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Roz-mov'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '180 - 250 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iulie - Septembrie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'cotoneaster-horizontalis' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Fructe roșii / Frunziș verde'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '50 - 80 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1 x 1 m'],
            ],
            'berberis-thunbergii-atropurpurea' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Frunziș roșu-purpuriu'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '120 - 150 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Aprilie - Mai'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '0.8 x 0.8 m'],
            ],
            'buxus-sempervirens-merisor' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Veșnic verde'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '100 - 200 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Aprilie - Mai'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '0.3 x 0.3 m'],
            ],
            'spiraea-vanhouttei' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb (cascadă)'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 200 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.2 x 1.2 m'],
            ],
            'spiraea-japonica-goldflame' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Flori roz / Frunziș auriu'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '60 - 80 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iunie - August'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '0.6 x 0.6 m'],
            ],
            'buddleja-davidii-liliac-de-vara' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Mov-violet parfumat'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '200 - 250 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iulie - Septembrie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'weigela-florida-bristol-ruby' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Roșu-rubin'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 200 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Mai - Iunie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'forsythia-lynwood' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Galben intens'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '200 - 250 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Martie - Aprilie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'hortensia-paniculata-vanille-fraise' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb-roz bicolor'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 200 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iulie - Octombrie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.2 x 1.2 m'],
            ],
            'hortensia-macrophylla-endless-summer' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Roz / Albastru'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '100 - 150 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iunie - Septembrie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1 x 1 m'],
            ],
            'hortensia-limelight' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Verde-lime / Alb'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '180 - 220 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iulie - Octombrie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'hortensia-annabelle' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Alb pur'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '120 - 150 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Iunie - Septembrie'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.2 x 1.2 m'],
            ],
            'photinia-red-robin' => [
                'culoare'                => ['name' => 'Culoare', 'value' => 'Frunziș roșu-aprins'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '200 - 300 cm'],
                'perioada-de-inflorire'  => ['name' => 'Perioadă de înflorire', 'value' => 'Aprilie - Mai'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '0.8 x 0.8 m'],
            ],

            // Berry Shrubs
            'coristi-corn-cornus-mas' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'August - Septembrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '250 - 350 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2.5 x 2 m'],
            ],
            'caprifoi-albastru-berry-blue' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Mai - Iunie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '120 - 150 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.2 m'],
            ],
            'catina-alba-leikora' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '200 - 300 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2 x 1.5 m'],
            ],
            'aronia-nero' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'August - Septembrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 200 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.2 m'],
            ],
            'goji-lycium-barbarum' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - Octombrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '180 - 250 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1.5 m'],
            ],
            'afin-chandler' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - August'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 180 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'afin-duke' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iunie - Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '130 - 160 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'afin-bluecrop' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - August'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 180 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'agris-hinnonmaki-verde' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '100 - 130 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'agris-hinnonmaki-rosu' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '100 - 130 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'coacaz-alb-blanka' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '120 - 150 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'coacaz-rosu-rovada' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - August'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '130 - 160 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'coacaz-rosu-jonkheer-van-tets' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iunie - Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '120 - 150 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'coacaz-negru-ben-sarek' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '100 - 120 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'coacaz-negru-titania' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '130 - 160 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 1 m'],
            ],
            'mur-loch-ness' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'August - Septembrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 200 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2 x 1.2 m'],
            ],
            'mur-thornfree' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'August - Septembrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '180 - 220 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2 x 1.2 m'],
            ],
            'mur-navaho-fara-spini' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - August'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 180 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '2 x 1 m'],
            ],
            'zmeur-galben-fallgold' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - Octombrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '140 - 170 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 0.5 m'],
            ],
            'zmeur-willamette' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iunie - Iulie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 180 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 0.5 m'],
            ],
            'zmeur-polka' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - Octombrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '130 - 160 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 0.5 m'],
            ],
            'zmeur-remontant-polka' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - Octombrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '130 - 160 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 0.5 m'],
            ],
            'zmeur-heritage' => [
                'perioada-de-coacere'    => ['name' => 'Perioadă de coacere', 'value' => 'Iulie - Octombrie'],
                'inaltime-la-maturitate' => ['name' => 'Înălțime la maturitate', 'value' => '150 - 180 cm'],
                'distanta-de-plantare'   => ['name' => 'Distanță de plantare', 'value' => '1.5 x 0.5 m'],
            ],

            // Fruit Trees
            'piersic-cardinal' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Piersic franc'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Autofertil'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'cais-harcot' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Zarzăr'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Iulie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Autofertil'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 4 m'],
            ],
            'cais-tomcot' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Zarzăr'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Iunie - Iulie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Harcot, Dacia'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 4 m'],
            ],
            'cires-kordia' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Mahaleb'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Iunie - Iulie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Van, Regina'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '5 x 4 m'],
            ],
            'cires-van' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Mahaleb'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Iunie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Stella, Kordia'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '5 x 4 m'],
            ],
            'cires-germersdorf' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Mahaleb'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Iunie - Iulie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Van, Stella'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '5 x 4 m'],
            ],
            'prun-tuleu-gras' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Mirobolan'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'August'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Stanley, Anna Späth'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 4 m'],
            ],
            'prun-anna-spath' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Mirobolan'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Autofertil'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 4 m'],
            ],
            'par-santa-maria' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Gutui A'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'August'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Williams, Abate Fetel'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'par-abate-fetel' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Gutui A'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Williams, Conference'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'par-conference' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Gutui A'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Williams, Parțial autofertil'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'par-williams' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'Gutui A'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'August - Septembrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Conference, Santa Maria'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-pinova' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie - Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Golden Delicious, Gala'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-granny-smith' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Golden Delicious, Jonathan'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-fuji' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Golden Delicious, Gala'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-gala' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Golden Delicious, Fuji'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-starkrimson' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie - Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Golden Delicious, Jonathan'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-generos' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie - Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Florina, Idared'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-jonathan' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie - Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Golden Delicious, Idared'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
            'mar-golden-delicious' => [
                'portaltoi'            => ['name' => 'Portaltoi', 'value' => 'MM106'],
                'perioada-de-coacere'  => ['name' => 'Perioadă de coacere', 'value' => 'Septembrie - Octombrie'],
                'polenizatori'         => ['name' => 'Polenizatori', 'value' => 'Jonathan, Idared, Gala'],
                'distanta-de-plantare' => ['name' => 'Distanță de plantare', 'value' => '4 x 3 m'],
            ],
        ];
    }

    public function up(): void
    {
        if (!$this->hasTable('pcs_posts') || !$this->hasTable('pcs_postmeta')) {
            return;
        }

        foreach ($this->getProductAttributesMap() as $slug => $specs) {
            $escapedSlug = addslashes($slug);
            $row = $this->fetchRow("
                SELECT `ID` FROM `pcs_posts`
                WHERE `post_type` = 'product' AND `post_name` = '{$escapedSlug}'
                LIMIT 1
            ");

            if (!$row) {
                continue;
            }

            $postId = (int) $row['ID'];
            $attributes = [];
            $pos = 0;

            foreach ($specs as $attrKey => $attrData) {
                $attributes[$attrKey] = [
                    'name'         => $attrData['name'],
                    'value'        => $attrData['value'],
                    'position'     => $pos++,
                    'is_visible'   => 1,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                ];
            }

            $serialized = addslashes(serialize($attributes));

            $existingMeta = $this->fetchRow("
                SELECT `meta_id` FROM `pcs_postmeta`
                WHERE `post_id` = {$postId} AND `meta_key` = '_product_attributes'
                LIMIT 1
            ");

            if ($existingMeta) {
                $metaId = (int) $existingMeta['meta_id'];
                $this->execute("
                    UPDATE `pcs_postmeta`
                    SET `meta_value` = '{$serialized}'
                    WHERE `meta_id` = {$metaId};
                ");
            } else {
                $this->execute("
                    INSERT INTO `pcs_postmeta` (`post_id`, `meta_key`, `meta_value`)
                    VALUES ({$postId}, '_product_attributes', '{$serialized}');
                ");
            }
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('pcs_posts') || !$this->hasTable('pcs_postmeta')) {
            return;
        }

        $slugs = array_keys($this->getProductAttributesMap());
        $slugs = array_filter($slugs, fn(string $s) => $s !== 'cornus-alba-sibirica' && $s !== 'zmeur-remontant-polka');
        if (empty($slugs)) {
            return;
        }

        $escapedSlugs = array_map(fn(string $s) => "'" . addslashes($s) . "'", $slugs);
        $slugList = implode(', ', $escapedSlugs);

        $rows = $this->fetchAll("
            SELECT `ID` FROM `pcs_posts`
            WHERE `post_type` = 'product' AND `post_name` IN ({$slugList})
        ");

        foreach ($rows as $row) {
            $postId = (int) $row['ID'];
            $this->execute("
                DELETE FROM `pcs_postmeta`
                WHERE `post_id` = {$postId} AND `meta_key` = '_product_attributes';
            ");
        }
    }
}
