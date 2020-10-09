<?php
namespace App\Controllers\Components;

use App\Models\ItemSortModel;

class CommonComponent extends AppComponent
{
    /**
     * Update sort items
     *
     * @param int $type
     * @param array $items
     * @param int $categoryId
     */
    public function updateSortItems($type, $items, $categoryId) {
        $sortValue = !empty($items[0]['sort'])? (int)$items[0]['sort'] : 0;
        foreach ($items as $item) {
            if (empty($item['sort'])) {
                $itemExst = ItemSortModel::factory()->where('foreign_type_id', $categoryId)->where('foreign_id', $item['id'])->where('type', $type)->limit(1)->findAll()->first();
                if (empty($itemExst)) {
                    $sortValue -= 1;
                    ItemSortModel::factory()->setAttributes([
                        'foreign_type_id' => $categoryId,
                        'foreign_id' => $item['id'],
                        'type' => $type,
                        'sort' => $sortValue
                    ])->insert();
                }
    
            }
        }
    }
}