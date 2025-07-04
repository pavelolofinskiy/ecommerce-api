<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ProductImage;

class ImportProductsFromXml extends Command
{
    protected $signature = 'import:products-xml';
    protected $description = 'Импорт товаров и категорий из XML по ссылке';

    public function handle()
    {
        $url = 'https://droneseucorporation.prom.ua/products_feed.xml?hash_tag=e6925aec334699ee8c9a3ffcfc726937&sales_notes=&product_ids=&label_ids=&exclude_fields=&html_description=0&yandex_cpa=&process_presence_sure=&languages=uk&extra_fields=&group_ids=';

        $response = Http::get($url);

        if ($response->failed()) {
            $this->error("Ошибка загрузки XML с URL: {$url}");
            return 1;
        }

        $xml = simplexml_load_string($response->body());
        if (!$xml) {
            $this->error("Ошибка парсинга XML");
            return 1;
        }

        $categoryMap = [];
        foreach ($xml->shop->categories->category as $cat) {
            $categoryId = (int) $cat['id'];
            $categoryName = (string) $cat;

            $category = Category::updateOrCreate(
                ['external_id' => $categoryId],
                ['name' => $categoryName]
            );

            $categoryMap[$categoryId] = $category->id;
        }

        $count = 0;
        foreach ($xml->shop->offers->offer as $item) {
            $externalId = (int) $item['id'];
            $name = (string) $item->name;
            $description = (string) $item->description;
            $price = (float) $item->price;
            $categoryId = (int) $item->categoryId;
            $url = (string) $item->url;

            $slug = null;
            if ($url) {
                // Парсим URL и извлекаем basename без `.html`
                $slug = basename(parse_url($url, PHP_URL_PATH), '.html');
            }


            $product = Product::updateOrCreate(
                ['external_id' => $externalId],
                [
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'category_id' => $categoryMap[$categoryId] ?? null,
                    'slug' => $slug,
                ]
            );

            $product->prices()->updateOrCreate(
                ['type' => 'default'], // или другой идентификатор типа цены
                ['amount' => $price]
            );

            $product->images()->delete();

            // Добавляем новые изображения
            foreach ($item->picture as $picture) {
                $product->images()->create([
                    'url' => (string) $picture
                ]);
            }

            // Импорт характеристик
            foreach ($item->param as $param) {
                $attrName = (string) $param['name'];
                $attrValue = (string) $param;

                $attribute = Attribute::firstOrCreate([
                    'name' => $attrName
                ]);

                DB::table('attribute_product')->updateOrInsert(
                    [
                        'product_id' => $product->id,
                        'attribute_id' => $attribute->id
                    ],
                    [
                        'value' => $attrValue,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }

            $count++;
        }

        $this->info("Импортировано товаров: {$count}");
        return 0;
    }
}