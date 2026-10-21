PRAGMA foreign_keys = ON;

INSERT OR IGNORE INTO genres (name) VALUES ('Comedy');
INSERT OR IGNORE INTO genres (name) VALUES ('Drama');
INSERT OR IGNORE INTO genres (name) VALUES ('Action');

INSERT INTO users (name, email, gender) VALUES ('Максим Сандин', 'sandinmaksim@yandex.ru', 'male');
INSERT INTO users (name, email, gender) VALUES ('Сосед Один', 'neighbor1@example.com', 'male');
INSERT INTO users (name, email, gender) VALUES ('Сосед Два', 'neighbor2@example.com', 'female');
INSERT INTO users (name, email, gender) VALUES ('Сосед Три', 'neighbor3@example.com', 'male');
INSERT INTO users (name, email, gender) VALUES ('Сосед Четыре', 'neighbor4@example.com', 'female');

INSERT INTO movies (title, year) VALUES ('Мой новый фильм 1 (2026)', 2026);
INSERT INTO movie_genres (movie_id, genre_id) VALUES ((SELECT movie_id FROM movies WHERE title = 'Мой новый фильм 1 (2026)'), (SELECT genre_id FROM genres WHERE name = 'Comedy'));

INSERT INTO movies (title, year) VALUES ('Мой новый фильм 2 (2026)', 2026);
INSERT INTO movie_genres (movie_id, genre_id) VALUES ((SELECT movie_id FROM movies WHERE title = 'Мой новый фильм 2 (2026)'), (SELECT genre_id FROM genres WHERE name = 'Drama'));

INSERT INTO movies (title, year) VALUES ('Мой новый фильм 3 (2026)', 2026);
INSERT INTO movie_genres (movie_id, genre_id) VALUES ((SELECT movie_id FROM movies WHERE title = 'Мой новый фильм 3 (2026)'), (SELECT genre_id FROM genres WHERE name = 'Action'));

INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ((SELECT user_id FROM users WHERE email = 'sandinmaksim@yandex.ru'), (SELECT movie_id FROM movies WHERE title = 'Мой новый фильм 1 (2026)'), 5.0, strftime('%s', 'now'));
INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ((SELECT user_id FROM users WHERE email = 'sandinmaksim@yandex.ru'), (SELECT movie_id FROM movies WHERE title = 'Мой новый фильм 2 (2026)'), 4.5, strftime('%s', 'now'));
INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ((SELECT user_id FROM users WHERE email = 'sandinmaksim@yandex.ru'), (SELECT movie_id FROM movies WHERE title = 'Мой новый фильм 3 (2026)'), 3.5, strftime('%s', 'now'));
