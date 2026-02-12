-- Insertando administrador para pruebas
INSERT INTO usuario(
    nombre,
    apellido,
    rol,
    email,
    password,
    activo
) VALUES (
    "Juan",
    "Perez",
    "Admin",
    "juancitoPerez@gmail.com",
    "$2y$10$7sVTWkNIelav6.OAR8J95u/b8te6WolSj9EQ4fZNIhSPlPRwueVZ2",
    1
);