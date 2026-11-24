<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Populate rose products with botanical attributes
 * (Culoare, Parfum, Înălțime la maturitate, Perioadă de înflorire)
 * so customers can filter roses by colour and fragrance and view specs on cards/PDP.
 */
final class SeedRoseProductAttributes extends AbstractMigration
{
    /**
     * Map of rose product slugs to their botanical attributes.
     *
     * @return array<string, array{color: string, fragrance: string, height: string, bloom: string}>
     */
    private function getRoseAttributesMap(): array
    {
        return [
            'trandafir-eiffel-tower' => [
                'color'     => 'Roz',
                'fragrance' => 'Intens',
                'height'    => '100 - 130 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-landora' => [
                'color'     => 'Galben',
                'fragrance' => 'Discret',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-duftwolke-fragrant-cloud' => [
                'color'     => 'Roșu',
                'fragrance' => 'Intens',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-osiria' => [
                'color'     => 'Bicolor',
                'fragrance' => 'Moderat',
                'height'    => '80 - 110 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-pink-panther' => [
                'color'     => 'Roz',
                'fragrance' => 'Moderat',
                'height'    => '90 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-grande-amore' => [
                'color'     => 'Roșu',
                'fragrance' => 'Moderat',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-super-star' => [
                'color'     => 'Portocaliu',
                'fragrance' => 'Intens',
                'height'    => '90 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-piccadilly' => [
                'color'     => 'Bicolor',
                'fragrance' => 'Discret',
                'height'    => '70 - 90 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-kronenbourg' => [
                'color'     => 'Bicolor',
                'fragrance' => 'Moderat',
                'height'    => '80 - 110 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-red-intuition' => [
                'color'     => 'Roșu',
                'fragrance' => 'Discret',
                'height'    => '90 - 110 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-lolita' => [
                'color'     => 'Portocaliu',
                'fragrance' => 'Moderat',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-tropicana' => [
                'color'     => 'Portocaliu',
                'fragrance' => 'Intens',
                'height'    => '90 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-elina' => [
                'color'     => 'Galben',
                'fragrance' => 'Discret',
                'height'    => '90 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-papa-meilland' => [
                'color'     => 'Roșu',
                'fragrance' => 'Intens',
                'height'    => '90 - 110 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-chrysler-imperial' => [
                'color'     => 'Roșu',
                'fragrance' => 'Intens',
                'height'    => '90 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-blue-moon' => [
                'color'     => 'Mov / Lila',
                'fragrance' => 'Intens',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-pascali' => [
                'color'     => 'Alb',
                'fragrance' => 'Discret',
                'height'    => '80 - 110 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-gloria-dei' => [
                'color'     => 'Galben',
                'fragrance' => 'Moderat',
                'height'    => '100 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-mister-lincoln' => [
                'color'     => 'Roșu',
                'fragrance' => 'Intens',
                'height'    => '100 - 130 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-double-delight' => [
                'color'     => 'Bicolor',
                'fragrance' => 'Intens',
                'height'    => '80 - 110 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-black-baccara-2' => [
                'color'     => 'Roșu',
                'fragrance' => 'Discret',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-black-baccara' => [
                'color'     => 'Roșu',
                'fragrance' => 'Discret',
                'height'    => '80 - 100 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-peace' => [
                'color'     => 'Galben',
                'fragrance' => 'Moderat',
                'height'    => '100 - 120 cm',
                'bloom'     => 'Mai - Octombrie (repetată)',
            ],
            'trandafir-urcator-pauls-scarlet' => [
                'color'     => 'Roșu',
                'fragrance' => 'Moderat',
                'height'    => '250 - 350 cm',
                'bloom'     => 'Iunie - Septembrie',
            ],
        ];
    }

    public function up(): void
    {
        if (!$this->hasTable('pcs_posts') || !$this->hasTable('pcs_postmeta')) {
            return;
        }

        foreach ($this->getRoseAttributesMap() as $slug => $specs) {
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

            $attributes = [
                'culoare' => [
                    'name'         => 'Culoare',
                    'value'        => $specs['color'],
                    'position'     => 0,
                    'is_visible'   => 1,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                ],
                'parfum' => [
                    'name'         => 'Parfum',
                    'value'        => $specs['fragrance'],
                    'position'     => 1,
                    'is_visible'   => 1,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                ],
                'inaltime-la-maturitate' => [
                    'name'         => 'Înălțime la maturitate',
                    'value'        => $specs['height'],
                    'position'     => 2,
                    'is_visible'   => 1,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                ],
                'perioada-de-inflorire' => [
                    'name'         => 'Perioadă de înflorire',
                    'value'        => $specs['bloom'],
                    'position'     => 3,
                    'is_visible'   => 1,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                ],
            ];

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

        $slugs = array_map(fn(string $s) => "'" . addslashes($s) . "'", array_keys($this->getRoseAttributesMap()));
        $slugList = implode(', ', $slugs);

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
