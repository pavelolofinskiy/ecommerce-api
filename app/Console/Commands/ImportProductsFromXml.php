<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ImportProductsFromXml extends Command
{
    protected $signature = 'import:products-xml {file}';
    protected $description = 'Импорт товаров из XML файла';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $xml = simplexml_load_file($filePath);
        if (!$xml) {
            $this->error("Не удалось загрузить XML");
            return 1;
        }

        $count = 0;

        foreach ($xml->product as $item) {
            $name = (string) $item->name;
            $description = (string) $item->description;
            $price = (float) $item->price;
            $categoryName = (string) $item->category;

            // Найти или создать категорию
            $category = Category::firstOrCreate(['name' => $categoryName]);

            // Создать или обновить товар по имени (или другому уникальному полю)
            $product = Product::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'price' => $price,
                    'category_id' => $category->id,
                ]
            );

            $count++;
        }

        $this->info("Импортировано товаров: {$count}");
        return 0;
    }
}
