<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Автоматический парсинг Iframe</title>
</head>
<body class="p-8 bg-gray-100 font-sans">
<div>
    <h1 class="text-xl font-bold mb-4">Попытка получить данные из Iframe (Wildberries)</h1>
    <p>Iframe 1 (Внимание: доступ заблокирован политикой Same-Origin Policy):</p>
    <iframe id="targetIframe"
            src="https://www.wildberries.ru/__internal/u-search/exactmatch/sng/common/v18/search?ab_testing=false&ab_testing=false&appType=1&curr=rub&dest=286&hide_dflags=131072&hide_dtype=11&inheritFilters=false&lang=ru&page=1&query=menu_redirect_subject_v2_9492%20%D0%BD%D0%BE%D1%83%D1%82%D0%B1%D1%83%D0%BA%D0%B8&resultset=catalog&sort=popular&spp=30&suppressSpellcheck=false"
            width="100%" height="400" frameborder="0" loading="lazy">
    </iframe>

    <button onclick="getDataFromIframe()">
        get
    </button>

    <div class="mt-4 p-3 bg-white border rounded shadow-md">
        <p class="font-semibold">Ответ (Iframe Content):</p>
        <div id="answer" class="text-sm text-gray-700 whitespace-pre-wrap">
            Ожидание попытки...
        </div>
    </div>

    <p class="text-red-600 mt-2">
        Обратите внимание: Почти во всех современных браузерах чтение содержимого iframe
        с другого домена будет заблокировано (Same-Origin Policy).
        В консоли браузера вы увидите ошибку безопасности.
    </p>
</div>

<script>
    // URL для перезагрузки iframe
    const iframeUrl = "https://www.wildberries.ru/__internal/u-search/exactmatch/sng/common/v18/search?ab_testing=false&ab_testing=false&appType=1&curr=rub&dest=286&hide_dflags=131072&hide_dtype=11&inheritFilters=false&lang=ru&page=1&query=menu_redirect_subject_v2_9492%20%D0%BD%D0%BE%D1%83%D1%82%D0%B1%D1%83%D0%BA%D0%B8&resultset=catalog&sort=popular&spp=30&suppressSpellcheck=false";
    const iframe = document.getElementById('targetIframe');
    const answerDiv = document.getElementById('answer');
    let attemptCount = 0;

    // Функция, которая пытается получить данные из iframe
    function getDataFromIframe() {
        attemptCount++;
        answerDiv.innerHTML = `Попытка #${attemptCount}. Чтение iframe...`;

        try {
            // *** ЭТОТ БЛОК БУДЕТ ЗАБЛОКИРОВАН BROWSER ***
            // contentDocument доступен ТОЛЬКО, если домены совпадают.
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

            if (iframeDoc) {
                const content = iframeDoc.body.innerHTML;
                answerDiv.textContent = "УСПЕХ! Извлеченное содержимое: " + content.substring(0, 500) + "...";
                console.log("Контент iframe:", content);
            } else {
                answerDiv.textContent = `Попытка #${attemptCount}: Документ iframe еще не загружен или доступ заблокирован.`;
            }
        } catch (error) {
            // ЭТА ОШИБКА БУДЕТ ГЕНЕРИРОВАНА БРАУЗЕРОМ ИЗ-ЗА SOP
            answerDiv.textContent = `Попытка #${attemptCount}: ОШИБКА БЕЗОПАСНОСТИ (Same-Origin Policy). Невозможно прочитать содержимое с ${iframe.src}. Подробнее смотрите в консоли браузера.`;
            console.error("SOP ОШИБКА:", error);
        }

        // 2. Требуемое действие: перезагрузить iframe, чтобы начать заново
        // (Это лишь имитирует "начать заново", но не решает проблему SOP)
        iframe.src = iframeUrl;
    }

    // 1. Запуск автоматического цикла каждые 0.2 секунды
    // Запускаем getDataFromIframe каждые 200 миллисекунд
    // Вы можете изменить интервал (200) по необходимости.
    // const intervalId = setInterval(getDataFromIframe, 200);

    // Пример остановки цикла через 10 секунд (для предотвращения бесконечной работы)
    // setTimeout(() => {
    //     clearInterval(intervalId);
    //     answerDiv.textContent += "\n\nАвтоматический цикл остановлен через 10 секунд.";
    // }, 10000);
</script>
<script src="https://cdn.tailwindcss.com"></script>
</body>
</html>
