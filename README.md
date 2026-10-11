** Задание 1 **

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
