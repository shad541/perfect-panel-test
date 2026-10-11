## Задание 1 ##
```
SELECT
    u.id AS ID,
    CONCAT(u.first_name, ' ', u.last_name) AS Name,
    MAX(b.author) AS Author,
    GROUP_CONCAT(b.name SEPARATOR ', ') AS Books
FROM user_books AS ub
INNER JOIN users AS u ON u.id = ub.user_id
INNER JOIN books AS b ON b.id = ub.book_id
WHERE u.birthday BETWEEN (CURDATE() - INTERVAL 17 YEAR) AND (CURDATE() - INTERVAL 7 YEAR)
GROUP BY u.id
HAVING COUNT(ub.id) = 2
    AND COUNT(DISTINCT b.author) = 1
    AND SUM(ub.return_date IS NULL OR ub.return_date >= ub.get_date + INTERVAL 2 WEEK) = 0
```

## Задание 2 ##

Как запустить:

все команды - в директории с проектом
сервис поднимается на 8000 порту от пользователя UID,GID=1000 (можно поменять в compose.yml)

запуск сервиса:

`docker compose up -d --build`

установка зависимостей:

`docker compose exec php composer install`

поргон тестов:

`docker compose exec php php bin/phpunit`

Запрос курсов:

`curl -H "Authorization: Bearer VrHbghos4HcDl2n_6dhfWpE0WniimQAzXXi0a_h15Y-2DkFzK1lWs6hdMwvcqnsR" "localhost:8000/api/v1?method=rates&currency=BTC,ETH"`

Запрос на конвертацию:

`curl -X POST -H "Authorization: Bearer VrHbghos4HcDl2n_6dhfWpE0WniimQAzXXi0a_h15Y-2DkFzK1lWs6hdMwvcqnsR" -d "currency_from=BTC&currency_to=USD&value=1" "localhost:8000/api/v1?method=convert"`

В рамках тестового задания:
- С деньгами лучше использовать bcmath.
- Кэширование и фоллбэк курсов не делал.
- Токен захардкодил в .env и положил его в git
- Состав токена не валидируется - достаточно сравнить с токеном из запроса.
- Security Component от Symfony сознательно не использовал.

Замечания:
Совсем маленькие числа при конвертации попадают в ответ в виде `1.17968e-5` . Альтернатива - возвращать в виде строки, либо писать собственный json_encode.
