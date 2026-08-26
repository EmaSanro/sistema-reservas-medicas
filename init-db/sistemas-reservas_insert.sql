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
    "$2a$12$zYmsdEUMjUQXm88n.rSZ2u//DiSa.SitKC7lMb3fs9swnR9XXKWba",
    1
);