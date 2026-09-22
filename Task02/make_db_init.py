import csv
import os

files = {
    'movies.csv': ('movies', 'id INTEGER, title TEXT, genres TEXT'),
    'ratings.csv': ('ratings', 'user_id INTEGER, movie_id INTEGER, rating REAL, timestamp INTEGER'),
    'tags.csv': ('tags', 'user_id INTEGER, movie_id INTEGER, tag TEXT, timestamp INTEGER'),
    'users.txt': ('users', 'id INTEGER, name TEXT, email TEXT, gender TEXT, register_date TEXT, occupation TEXT')
}

delimiters = {
    'movies.csv': ',',
    'ratings.csv': ',',
    'tags.csv': ',',
    'users.txt': '|'
}

def escape(val):
    return "'" + str(val).replace("'", "''") + "'"

with open('db_init.sql', 'w', encoding='utf-8') as out:
    for filename, (table, schema) in files.items():
        out.write(f"DROP TABLE IF EXISTS {table};\n")
        out.write(f"CREATE TABLE {table} ({schema});\n")

    for filename, (table, schema) in files.items():
        if not os.path.exists(filename):
            print(f"Файл {filename} не найден.")
            continue
        delim = delimiters.get(filename, ',')
        with open(filename, 'r', encoding='utf-8') as f:
            reader = csv.reader(f, delimiter=delim)
            next(reader, None)
            for row in reader:
                vals = ", ".join([escape(v) for v in row])
                out.write(f"INSERT INTO {table} VALUES ({vals});\n")

print("Файл db_init.sql создан.")