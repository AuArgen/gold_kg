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
    let latestProductId = 0; // Наш внутренний ID самого нового загруженного товара

    function createProductCard(product) {
        const creationDate = new Date(product.created_at).toLocaleString('ru-RU');

        const link = document.createElement('a');
        // Используем product.url, если есть, иначе формируем из product_id
        link.href = product.url || `https://www.wildberries.ru/catalog/${product.product_id}/detail.aspx`;
        link.target = "_blank"; // Открывать в новой вкладке
        link.rel = "noopener noreferrer";
        link.className = "block bg-white p-4 rounded-lg shadow-md transition-transform transform hover:-translate-y-1 relative";
        link.setAttribute('data-internal-id', product.id); // Используем наш внутренний ID

        let priceDisplay = `<p class="text-green-600 font-semibold mt-2">Цена: ${product.currentPrice / 100} руб.</p>`;
        if (product.oldPrice && product.oldPrice > product.currentPrice) {
            priceDisplay = `
                <p class="text-gray-500 line-through text-sm">${product.oldPrice / 100} руб.</p>
                <p class="text-green-600 font-bold text-lg">${product.currentPrice / 100} руб.</p>
            `;
        }

        let discountBadge = '';
        if (product.discountPercentage && product.discountPercentage > 0) {
            discountBadge = `<span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">- ${product.discountPercentage}%</span>`;
        }

        let newBadge = '';
        if (product.isNew) {
            newBadge = `<span class="absolute top-2 left-2 bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full">Новинка</span>`;
        }

        let goodPriceBadge = '';
        if (product.isGoodPrice) {
            goodPriceBadge = `<span class="absolute bottom-2 right-2 bg-purple-500 text-white text-xs font-bold px-2 py-1 rounded-full">Выгодная цена</span>`;
        }

        let actionPromotionDisplay = '';
        if (product.actionPromotion) {
            actionPromotionDisplay = `<p class="text-orange-500 text-xs mt-1">${product.actionPromotion}</p>`;
        }

        let ratingDisplay = '';
        if (product.rating !== null && product.reviewCount !== null) {
            ratingDisplay = `
                <div class="flex items-center mt-2">
                    <span class="text-yellow-500">★</span>
                    <span class="ml-1 text-gray-700">${product.rating} (${product.reviewCount} отзывов)</span>
                </div>
            `;
        }

        link.innerHTML = `
            ${discountBadge}
            ${newBadge}
            <img src="${product.imageUrl || 'https://via.placeholder.com/150'}" alt="${product.name}" class="w-full h-48 object-contain mb-4 rounded">
            <h3 class="text-lg font-bold text-gray-800">${product.title || product.name}</h3>
            <p class="text-gray-600">Бренд: ${product.brand || 'Неизвестно'}</p>
            ${priceDisplay}
            ${ratingDisplay}
            ${actionPromotionDisplay}
            ${goodPriceBadge}
            <p class="text-xs text-gray-400 mt-2">Добавлено: ${creationDate}</p>
        `;
        return link;
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
        // Обновляем ID самого нового товара
        if (products.length > 0) {
            latestProductId = products[0].id; // Обновляем наш внутренний ID
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
                if (latestProductId === 0) {
                    latestProductId = result.data[0].id; // Устанавливаем наш внутренний ID самого первого товара
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
        if (latestProductId === 0) return; // Не проверять, если еще ничего не загружено

        try {
            const response = await fetch(`{{ route('products.latest') }}?lastId=${latestProductId}`);
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
    setInterval(checkForLatestProducts, 500);

</script>

</body>
</html>
