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
</div>

<script>
    const productsContainer = document.getElementById('products-container');

    async function fetchProducts() {
        try {
            const response = await fetch('{{ route('products.index') }}');
            const products = await response.json();

            // Сохраняем текущую прокрутку
            const scrollPosition = window.scrollY;

            productsContainer.innerHTML = ''; // Очищаем контейнер

            if (products.length === 0) {
                productsContainer.innerHTML = '<p class="text-gray-500">Товары не найдены.</p>';
                return;
            }

            products.forEach(product => {
                const productCard = `
                    <div class="bg-white p-4 rounded-lg shadow-md transition-transform transform hover:-translate-y-1">
                        <h3 class="text-lg font-bold text-gray-800">${product.name}</h3>
                        <p class="text-gray-600">Бренд: ${product.brand}</p>
                        <p class="text-green-600 font-semibold mt-2">Цена: ${product.price / 100} руб.</p>
                        <div class="flex items-center mt-2">
                            <span class="text-yellow-500">★</span>
                            <span class="ml-1 text-gray-700">${product.reviewRating} (${product.feedbacks} отзывов)</span>
                        </div>
                         <p class="text-sm text-gray-500 mt-1">В наличии: ${product.totalQuantity} шт.</p>
                    </div>
                `;
                productsContainer.innerHTML += productCard;
            });

            // Восстанавливаем прокрутку, чтобы страница не "прыгала"
            window.scrollTo(0, scrollPosition);

        } catch (error) {
            console.error('Ошибка при загрузке товаров:', error);
            productsContainer.innerHTML = '<p class="text-red-500">Не удалось загрузить товары. Проверьте консоль.</p>';
        }
    }

    // Загружаем товары при загрузке страницы и затем каждые 1 секунду
    fetchProducts();
    setInterval(fetchProducts, 1000);
</script>

</body>
</html>
