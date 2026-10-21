#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они ценили. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT u1.name AS user1, u2.name AS user2, m.title FROM ratings r1 JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id JOIN users u1 ON u1.id = r1.user_id JOIN users u2 ON u2.id = r2.user_id JOIN movies m ON m.id = r1.movie_id ORDER BY u1.name, u2.name, m.title LIMIT 100;"
echo " "

echo "2. Найти 10 самых свежих оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RankedRatings AS (SELECT r.user_id, r.movie_id, r.rating, r.timestamp, ROW_NUMBER() OVER (PARTITION BY r.user_id ORDER BY r.timestamp DESC) as rn FROM ratings r) SELECT m.title, u.name, rr.rating, date(rr.timestamp, 'unixepoch') AS rating_date FROM RankedRatings rr JOIN users u ON u.id = rr.user_id JOIN movies m ON m.id = rr.movie_id WHERE rr.rn = 1 ORDER BY rr.timestamp DESC LIMIT 10;"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке \"Рекомендуем\" для фильмов должно быть написано \"Да\" или \"Нет\"."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH AvgRatings AS (SELECT movie_id, AVG(rating) AS avg_rating FROM ratings GROUP BY movie_id), Extremes AS (SELECT MAX(avg_rating) AS max_avg, MIN(avg_rating) AS min_avg FROM AvgRatings) SELECT m.title, CASE WHEN TRIM(m.title) GLOB '*([0-9][0-9][0-9][0-9])' THEN CAST(SUBSTR(TRIM(m.title), LENGTH(TRIM(m.title)) - 4, 4) AS INTEGER) ELSE 0 END AS year, ar.avg_rating, CASE WHEN ar.avg_rating = e.max_avg THEN 'Да' ELSE 'Нет' END AS \"Рекомендуем\" FROM AvgRatings ar JOIN movies m ON m.id = ar.movie_id CROSS JOIN Extremes e WHERE ar.avg_rating = e.max_avg OR ar.avg_rating = e.min_avg ORDER BY year ASC, m.title ASC;"
echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-женщины в период с 2010 по 2012 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT COUNT(r.rating) AS total_ratings, AVG(r.rating) AS avg_rating FROM ratings r JOIN users u ON u.id = r.user_id WHERE u.gender = 'female' AND strftime('%Y', datetime(r.timestamp, 'unixepoch')) BETWEEN '2010' AND '2012';"
echo " "

echo "5. Составить список фильмов с указанием их средней оценки и места в рейтинге по средней оценке. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH MovieAvg AS (SELECT movie_id, AVG(rating) AS avg_rating, RANK() OVER (ORDER BY AVG(rating) DESC) AS rank FROM ratings GROUP BY movie_id) SELECT m.title, CASE WHEN TRIM(m.title) GLOB '*([0-9][0-9][0-9][0-9])' THEN CAST(SUBSTR(TRIM(m.title), LENGTH(TRIM(m.title)) - 4, 4) AS INTEGER) ELSE 0 END AS year, ma.avg_rating, ma.rank FROM MovieAvg ma JOIN movies m ON m.id = ma.movie_id ORDER BY year ASC, m.title ASC LIMIT 20;"
echo " "

echo "6. Вывести список из 10 последних зарегистрированных пользователей в формате \"Фамилия Имя|Дата регистрации\" (сначала фамилия, потом имя)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT substr(name, instr(name, ' ') + 1) || ' ' || substr(name, 1, instr(name, ' ') - 1) || '|' || register_date AS user_info FROM users ORDER BY register_date DESC LIMIT 10;"
echo " "

echo "7. С помощью рекурсивного CTE составить таблицу умножения для чисел от 1 до 10. Должен получиться один столбец следующего вида: 1x1=1 ... 10x10=100."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE nums(n) AS (SELECT 1 UNION ALL SELECT n + 1 FROM nums WHERE n < 10) SELECT n1.n || 'x' || n2.n || '=' || (n1.n * n2.n) AS multiplication FROM nums n1 CROSS JOIN nums n2 ORDER BY n1.n, n2.n;"
echo " "

echo "8. С помощью рекурсивного CTE выделить все жанры фильмов, имеющиеся в таблице movies (каждый жанр в отдельной строке)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE split_genres(movie_id, genre, rest) AS (SELECT id, '', genres || '|' FROM movies UNION ALL SELECT movie_id, substr(rest, 1, instr(rest, '|') - 1), substr(rest, instr(rest, '|') + 1) FROM split_genres WHERE rest <> '') SELECT DISTINCT genre FROM split_genres WHERE genre <> '' ORDER BY genre;"
echo " "
