<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Список товаров</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8 bg-gray-100 font-sans">

<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Список товаров</h1>
    <div id="products-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Товары будут загружены сюда -->
    </div>
    <div id="loader" class="text-center p-4" style="display: none;">Загрузка...</div>
    <div class="text-center mt-6">
        <button id="load-more-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" style="display: none;">
            Загрузить еще
        </button>
    </div>
</div>

<script>
    const productsContainer = document.getElementById('products-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loader = document.getElementById('loader');

    let currentPage = 1;
    let lastPage = 1;
    let latestTimestamp = null; // Временная метка самого нового товара

    function createProductCard(product) {
        const creationDate = new Date(product.created_at).toLocaleString('ru-RU');
        const card = document.createElement('div');
        card.className = "bg-white p-4 rounded-lg shadow-md transition-transform transform hover:-translate-y-1";
        card.setAttribute('data-product-id', product.id);
        card.innerHTML = `
            <h3 class="text-lg font-bold text-gray-800">${product.name}</h3>
            <p class="text-gray-600">Бренд: ${product.brand}</p>
            <p class="text-green-600 font-semibold mt-2">Цена: ${product.price / 100} руб.</p>
            <div class="flex items-center mt-2">
                <span class="text-yellow-500">★</span>
                <span class="ml-1 text-gray-700">${product.reviewRating} (${product.feedbacks} отзывов)</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">В наличии: ${product.totalQuantity} шт.</p>
            <p class="text-xs text-gray-400 mt-2">Добавлено: ${creationDate}</p>
        `;
        return card;
    }

    // Функция для добавления старых товаров в конец списка
    function appendProducts(products) {
        products.forEach(product => {
            productsContainer.appendChild(createProductCard(product));
        });
    }

    // Функция для добавления новых товаров в начало списка
    function prependProducts(products) {
        // Сортируем, чтобы самые новые были вверху
        products.reverse().forEach(product => {
            productsContainer.insertBefore(createProductCard(product), productsContainer.firstChild);
        });
        // Обновляем временную метку самого нового товара
        if (products.length > 0) {
            latestTimestamp = products[0].created_at;
        }
    }

    // Загрузка старых товаров (пагинация)
    async function fetchPaginatedProducts(page = 1) {
        try {
            loader.style.display = 'block';
            loadMoreBtn.style.display = 'none';

            const response = await fetch(`{{ route('products.index') }}?page=${page}`);
            const result = await response.json();

            if (page === 1 && result.data.length > 0) {
                // Устанавливаем временную метку самого первого товара
                if (!latestTimestamp) {
                    latestTimestamp = result.data[0].created_at;
                }
            }

            appendProducts(result.data);

            currentPage = result.current_page;
            lastPage = result.last_page;

            if (currentPage < lastPage) {
                loadMoreBtn.style.display = 'block';
            }

        } catch (error) {
            console.error('Ошибка при загрузке товаров:', error);
        } finally {
            loader.style.display = 'none';
        }
    }

    // Проверка наличия новых товаров
    async function checkForLatestProducts() {
        if (!latestTimestamp) return; // Не проверять, если еще ничего не загружено

        try {
            const response = await fetch(`{{ route('products.latest') }}?lastTimestamp=${latestTimestamp}`);
            const newProducts = await response.json();

            if (newProducts.length > 0) {
                prependProducts(newProducts);
            }
        } catch (error) {
            console.error('Ошибка при проверке новых товаров:', error);
        }
    }

    // Загружаем первую страницу
    fetchPaginatedProducts(currentPage);

    // Обработчик для кнопки "Загрузить еще"
    loadMoreBtn.addEventListener('click', () => {
        if (currentPage < lastPage) {
            fetchPaginatedProducts(currentPage + 1);
        }
    });

    // Запускаем проверку новых товаров каждые 3 секунды
    setInterval(checkForLatestProducts, 3000);

</script>

</body>
</html>
