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
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Список товаров</h1>
        <div class="text-lg">
            Найдено: <span id="total-products-count" class="font-bold">0</span>
        </div>
    </div>

    <!-- Панель фильтров -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label for="search-input" class="block text-sm font-medium text-gray-700">Поиск</label>
            <input type="text" id="search-input" placeholder="Название или бренд..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="brand-select" class="block text-sm font-medium text-gray-700">Бренд</label>
            <select id="brand-select" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Все бренды</option>
            </select>
        </div>
        <div>
            <label for="discount-input" class="block text-sm font-medium text-gray-700">Скидка от (%)</label>
            <input type="number" id="discount-input" min="0" max="100" placeholder="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>

    <div id="products-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
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
    const searchInput = document.getElementById('search-input');
    const brandSelect = document.getElementById('brand-select');
    const discountInput = document.getElementById('discount-input');
    const totalProductsCount = document.getElementById('total-products-count');

    let currentPage = 1;
    let lastPage = 1;
    let latestTimestamp = null;
    let searchTimeout;
    let isLoading = false;

    function createProductCard(product) {
        const updatedDate = new Date(product.updated_at).toLocaleString('ru-RU'); // Используем updated_at
        const link = document.createElement('a');
        link.href = product.url || `https://www.wildberries.ru/catalog/${product.product_id}/detail.aspx`;
        link.target = "_blank";
        link.rel = "noopener noreferrer";
        link.className = "block bg-white p-4 rounded-lg shadow-md transition-transform transform hover:-translate-y-1 relative";
        link.setAttribute('data-product-id', product.product_id);

        let priceChangeInfo = '';
        if (product.discountPercentage && product.discountPercentage > 0) {
            priceChangeInfo = `<p class="text-red-500 text-sm font-bold">↓ ${product.discountPercentage}%</p>`;
        }

        let priceDisplay = `<p class="text-green-600 font-semibold mt-2">${product.currentPrice} руб.</p>`;
        if (product.oldPrice && product.oldPrice > product.currentPrice) {
            priceDisplay = `
                <div class="flex items-baseline gap-2">
                    <p class="text-gray-500 line-through text-sm">${product.oldPrice} руб.</p>
                    <p class="text-green-600 font-bold text-lg">${product.currentPrice} руб.</p>
                    ${priceChangeInfo}
                </div>
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
            <p class="text-xs text-gray-400 mt-2">Обновлено: ${updatedDate}</p>
        `;
        return link;
    }

    function appendProducts(products) {
        products.forEach(product => productsContainer.appendChild(createProductCard(product)));
    }

    function prependOrUpdateProducts(products) {
        products.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));

        products.forEach(product => {
            const existingCard = document.querySelector(`[data-product-id='${product.product_id}']`);
            if (existingCard) {
                existingCard.remove();
            }
            productsContainer.insertBefore(createProductCard(product), productsContainer.firstChild);
        });

        if (products.length > 0) {
            latestTimestamp = products[0].updated_at;
        }
    }

    function areFiltersActive() {
        return searchInput.value.length > 0 || brandSelect.value !== '' || discountInput.value.length > 0;
    }

    async function fetchProducts(page = 1, isSearch = false) {
        if (isLoading) return;
        isLoading = true;

        if (isSearch) {
            productsContainer.innerHTML = '';
            currentPage = 1;
            latestTimestamp = null;
        }

        loader.style.display = 'block';
        loadMoreBtn.style.display = 'none';

        const params = new URLSearchParams({
            page: page,
            search: searchInput.value,
            brand: brandSelect.value,
            min_discount: discountInput.value
        });

        try {
            const response = await fetch(`{{ route('products.index') }}?${params.toString()}`);
            const result = await response.json();

            if (page === 1) {
                totalProductsCount.textContent = result.total;
                if (result.data.length > 0 && !latestTimestamp) {
                    latestTimestamp = result.data.reduce((max, p) => p.updated_at > max ? p.updated_at : max, result.data[0].updated_at);
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
            isLoading = false;
        }
    }

    async function loadBrands() {
        try {
            const response = await fetch(`{{ route('products.brands') }}`);
            const brands = await response.json();
            brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand;
                option.textContent = brand;
                brandSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Ошибка при загрузке брендов:', error);
        }
    }

    async function checkForLatestProducts() {
        if (areFiltersActive() || !latestTimestamp) {
            return;
        }

        try {
            const response = await fetch(`{{ route('products.latest') }}?lastTimestamp=${latestTimestamp}`);
            const newProducts = await response.json();

            if (newProducts.length > 0) {
                prependOrUpdateProducts(newProducts);
            }
        } catch (error) {
            console.error('Ошибка при проверке новых товаров:', error);
        }
    }

    function handleFilterChange() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchProducts(1, true);
        }, 500);
    }

    // Инициализация
    loadBrands();
    fetchProducts(1, true);

    // Слушатели событий
    searchInput.addEventListener('input', handleFilterChange);
    brandSelect.addEventListener('change', handleFilterChange);
    discountInput.addEventListener('input', handleFilterChange);

    loadMoreBtn.addEventListener('click', () => {
        if (currentPage < lastPage && !isLoading) {
            fetchProducts(currentPage + 1, false);
        }
    });

    setInterval(checkForLatestProducts, 500);

</script>

</body>
</html>
