<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Dictionary;
use App\Models\DictionaryItem;
use Illuminate\Database\Seeder;

final class DevelopmentGroupDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'group_format' => ['code' => 'development-test-format', 'name' => 'Технический тестовый формат'],
            'gender' => ['code' => 'development-test-gender', 'name' => 'Техническая тестовая аудитория'],
        ];

        foreach ($items as $dictionaryCode => $item) {
            $dictionary = Dictionary::query()->where('code', $dictionaryCode)->firstOrFail();
            DictionaryItem::query()->updateOrCreate(
                ['dictionary_id' => $dictionary->getKey(), 'code' => $item['code']],
                ['name' => $item['name'], 'sort_order' => 999, 'active' => true],
            );
        }
    }
}
