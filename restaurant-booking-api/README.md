Основные эндпоинты:
POST /api/register Регистрация
POST /api/login Вход
POST /api/logout Выход
GET /api/restaurants Список ресторанов
POST /api/restaurants Создание ресторана (admin)
GET /api/restaurants/{id} Детали ресторана
GET /api/restaurants/{id}/available-tables Свободные столики
GET /api/bookings Список броней
POST /api/bookings Создание брони
GET /api/my-bookings Мои брони
PATCH /api/bookings/{id}/cancel Отмена брони
POST /api/reviews Создание отзыва
GET /api/favorites Избранное
