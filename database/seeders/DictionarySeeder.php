<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Dictionary;
use Illuminate\Database\Seeder;

final class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['code' => 'education_type', 'name' => 'Тип образования', 'sort_order' => 10],
            ['code' => 'group_format', 'name' => 'Формат группы', 'sort_order' => 20],
            ['code' => 'gender', 'name' => 'Пол', 'sort_order' => 30],
        ];

        foreach ($definitions as $definition) {
            Dictionary::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'sort_order' => $definition['sort_order'],
                    'active' => true,
                ],
            );
        }
    }
}
