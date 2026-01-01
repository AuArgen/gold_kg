<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GoldPrice;
use App\Models\Gold;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParseGoldPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gold:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse gold prices from NBKR website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://www.nbkr.kg/printver.jsp?item=2746&lang=KGZ';
        $this->info("Starting parsing from: $url");
        echo "Lorem";
        // 1. ПОЛУЧЕНИЕ HTML-КОНТЕНТА
        $html = @file_get_contents($url);

        if (!$html) {
            $this->error('Failed to load data from the specified URL.');
            return 1;
        }

        // 2. НАСТРОЙКА ПАРСИНГА И МАССИВ ДЛЯ ХРАНЕНИЯ
        $insertedCount = 0;
        $errors = [];
        $uniqueDates = [];

        // Регулярное выражение для поиска строк таблицы, исключая заголовок
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);

        if (empty($rows[1])) {
             $this->error('No rows found in the table.');
             return 1;
        }

        // Удаляем первую строку (заголовок)
        array_shift($rows[1]);

        // Обрабатываем строки в обратном порядке (от старых к новым).
        $rowsToProcess = array_reverse($rows[1]);

        // 3. ОБРАБОТКА ДАННЫХ
        DB::beginTransaction();
        try {
            foreach ($rowsToProcess as $rowHtml) {
                // Извлекаем содержимое ячеек (<td>)
                preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $rowHtml, $cells);

                if (count($cells[1]) < 4) continue;

                // Очищаем и форматируем данные
                $dateString = trim(strip_tags($cells[1][0]));
                $weightString = trim(strip_tags($cells[1][1]));
                $buyInPriceString = trim(strip_tags($cells[1][2]));
                $salePriceString = trim(strip_tags($cells[1][3]));

                // Конвертируем дату в формат Y-m-d
                try {
                    $publicDate = Carbon::createFromFormat('d.m.Y', $dateString)->format('Y-m-d');
                } catch (\Exception $e) {
                    $errors[] = "Invalid date format: $dateString";
                    continue;
                }

                $uniqueDates[$publicDate] = true;

                // Находим ID золотого слитка по его весу
                $weightUnit = (int)round((float)$weightString * 10000); // 31.1035 * 10000 = 311035
                $goldItem = Gold::where('weight_units', $weightUnit)->first();

                if (!$goldItem) {
                    $errors[] = "Gold item not found for weight: {$weightString} grams.";
                    continue;
                }

                $goldId = $goldItem->id;

                // Конвертируем цены в копейки (умножаем на 100)
                $buyInKopecks = $this->convertPriceToKopecks($buyInPriceString);
                $saleKopecks = $this->convertPriceToKopecks($salePriceString);

                // Проверяем, существует ли уже запись с этой датой и gold_id
                $exists = GoldPrice::where('gold_id', $goldId)->whereDate('public_date', $publicDate)->exists();
                if ($exists) {
                    continue; // Пропускаем, чтобы не дублировать
                }

                // 4. РАСЧЕТ РАЗНИЦЫ (Сравниваем с предыдущим днем)
                $previousPrice = GoldPrice::where('gold_id', $goldId)
                    ->whereDate('public_date', '<', $publicDate) // Ищем предыдущую цену
                    ->orderBy('public_date', 'desc')
                    ->first();

                $differenceSaleKopecks = $previousPrice ? $saleKopecks - $previousPrice->sale_kopecks : 0;
                $differenceBuyInKopecks = $previousPrice ? $buyInKopecks - $previousPrice->buy_in_kopecks : 0;

                // 5. СОХРАНЕНИЕ
                GoldPrice::create([
                    'gold_id' => $goldId,
                    'sale_kopecks' => $saleKopecks,
                    'buy_in_kopecks' => $buyInKopecks,
                    'difference_sale_kopecks' => $differenceSaleKopecks,
                    'difference_buy_in_kopecks' => $differenceBuyInKopecks,
                    'public_date' => $publicDate,
                ]);
                $insertedCount++;
            }

            DB::commit();

            if (!empty($errors)) {
                $this->warn("Parsing completed with errors. Inserted: {$insertedCount}. Errors: " . implode('; ', $errors));
            } else {
                $datesList = implode(', ', array_keys($uniqueDates));
                $this->info("Successfully inserted {$insertedCount} new records for dates: {$datesList}");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error saving data: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Вспомогательная функция для конвертации цены из строки в копейки (целое число).
     */
    private function convertPriceToKopecks(string $priceString): int
    {
        // Удаляем пробелы (разделитель тысяч) и заменяем запятую на точку, если она есть.
        $cleaned = str_replace(' ', '', $priceString);
        $floatPrice = (float)str_replace(',', '.', $cleaned);

        // Умножаем на 100 и округляем до целого.
        return (int)round($floatPrice * 100);
    }
}
