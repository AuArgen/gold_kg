<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\GoldPrice;
use App\Models\Gold; // Используем модель Gold для поиска по весу
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IndexController extends Controller
{
    /**
     * Показывает список цен на золото.
     */
    public function gold(Request $request)
    {
        $prices = GoldPrice::with('gold')
            ->orderBy('public_date', 'desc')
            ->orderBy('gold_id', 'asc')
            ->paginate(100);

        // Статус-сообщение для вывода после парсинга
        $status = $request->session()->get('status');

        return view('admin.gold', compact('prices', 'status'));
    }

    /**
     * Обрабатывает POST-запрос на парсинг цен с внешнего сайта.
     */
    public function parseGold(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        // 1. ПОЛУЧЕНИЕ HTML-КОНТЕНТА
        $html = @file_get_contents($url);

        if (!$html) {
            return redirect()->route('admin.gold')->with('status', [
                'type' => 'error',
                'message' => 'Не удалось загрузить данные по указанному URL.'
            ]);
        }

        // 2. НАСТРОЙКА ПАРСИНГА И МАССИВ ДЛЯ ХРАНЕНИЯ
        $insertedCount = 0;
        $errors = [];
        $uniqueDates = [];

        // Регулярное выражение для поиска строк таблицы, исключая заголовок
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);

        // Удаляем первую строку (заголовок)
        array_shift($rows[1]);

        // **********************************************
        // ИСПРАВЛЕНИЕ: Обрабатываем строки в обратном порядке (от старых к новым).
        // Это гарантирует, что "предыдущая" дата уже будет в базе данных.
        $rowsToProcess = array_reverse($rows[1]);
        // **********************************************

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
                $publicDate = Carbon::createFromFormat('d.m.Y', $dateString)->format('Y-m-d');
                $uniqueDates[$publicDate] = true;

                // Находим ID золотого слитка по его весу
                $weightUnit = (int)round((float)$weightString * 10000); // 31.1035 * 10000 = 311035
                $goldItem = Gold::where('weight_units', $weightUnit)->first();

                if (!$goldItem) {
                    $errors[] = "Не найден ID слитка для веса: {$weightString} грамм. Проверьте таблицу 'gold'.";
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
                // Теперь ищется запись, которая была сохранена на предыдущем шаге цикла (предыдущая дата)
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
                $statusMessage = "Парсинг завершен. Вставлено записей: {$insertedCount}. Обнаружены ошибки: " . implode('; ', $errors);
                $statusType = 'warning';
            } else {
                $datesList = implode(', ', array_keys($uniqueDates));
                $statusMessage = "Успешно! Вставлено {$insertedCount} новых записей за даты: {$datesList}";
                $statusType = 'success';
            }

            return redirect()->route('admin.gold')->with('status', [
                'type' => $statusType,
                'message' => $statusMessage
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.gold')->with('status', [
                'type' => 'error',
                'message' => 'Ошибка при сохранении данных: ' . $e->getMessage()
            ]);
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
