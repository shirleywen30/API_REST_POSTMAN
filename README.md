# API REST con PHP y Seguridad JWT

Laboratorio de **Desarrollo de Software VII**.
API REST en PHP con autenticación mediante **JSON Web Tokens (JWT)**, contraseñas hasheadas con **bcrypt**, y CRUD completo de productos (GET, POST, PUT, DELETE) protegido por token.

## Tecnologías

- PHP 7+ (Apache vía WAMP)
- MySQL (PDO)
- Composer
- [`firebase/php-jwt`](https://github.com/firebase/php-jwt) v7
- Postman (pruebas)

## Estructura del proyecto

```
jwt-api/
├── .env                    
├── .gitignore
├── composer.json
├── config.php              # Carga el .env y define constantes globales
├── env.php                 # Función para leer el archivo .env
├── login.php               # Endpoint de autenticación, devuelve el token
├── index.php               # Front Controller: valida token y deriva a CRUD
└── src/
    ├── Database.php            # Conexión PDO (singleton)
    ├── AuthService.php         # Generación y validación de tokens JWT
    └── ProductController.php   # Lógica CRUD de productos
```

## Base de datos

```sql
CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL,
  producto VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL
);

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);
```

## Instalación

1. Clonar el repositorio dentro de la carpeta pública de WAMP/XAMPP:
   ```bash
   git clone https://github.com/shirleywen30/API_REST_POSTMAN.git
   cd jwt-api
   ```
2. Instalar dependencias:
   ```bash
   composer install
   ```
3. Crear el archivo `.env` en la raíz con:
   ```env
   JWT_SECRET_KEY=tu_clave_secreta_larga_y_aleatoria
   DB_HOST=localhost
   DB_NAME=jwt_api_db
   DB_USER=root
   DB_PASS=
   ```
4. Crear la base de datos y las tablas.
5. Crear un usuario administrador con contraseña hasheada (bcrypt) directamente en la base de datos o mediante un script temporal que use `password_hash()`.

## Endpoints

| Método | Endpoint | Descripción | Requiere token |
|---|---|---|---|
| POST | `/login.php` | Autentica usuario y devuelve un token JWT | No |
| GET | `/index.php` | Lista todos los productos | Sí |
| GET | `/index.php?id=1` | Obtiene un producto por id | Sí |
| POST | `/index.php` | Crea un nuevo producto | Sí |
| PUT | `/index.php?id=1` | Actualiza un producto existente | Sí |
| DELETE | `/index.php?id=1` | Elimina un producto | Sí |

## Autenticación

Todas las peticiones a `/index.php` requieren un header:

```
Authorization: Bearer <token>
```

El token se obtiene autenticándose en `/login.php`:

**Request**
```json
POST /login.php
{
  "username": "admin",
  "password": "admin123"
}
```

**Response**
```json
{
  "success": true,
  "message": "Login exitoso.",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

## Seguridad implementada

- **Contraseñas**: nunca se almacenan en texto plano. Se aplica `password_hash()` con `PASSWORD_BCRYPT` al crear el usuario, y `password_verify()` al momento del login.
- **Tokens JWT**: firmados con el algoritmo `HS256` usando una clave secreta almacenada fuera del repositorio (`.env`), con expiración de 1 hora.
- **Front Controller**: `index.php` centraliza la verificación del token antes de derivar cualquier petición al controlador correspondiente, usando una estructura `switch` según el método HTTP.
- **PDO con consultas preparadas**: previene inyección SQL en todas las operaciones de base de datos.
- **Control de errores**: cada operación responde siempre con una estructura JSON consistente (`success`, `message`, `data`, `errors`) y el código HTTP correspondiente (200, 201, 400, 401, 404, 422, 500).

## Pruebas con Postman

### Escenario negativo — Sin token

```
GET http://localhost/jwt-api/index.php
```
Respuesta esperada: `401 Unauthorized`

```json
{
  "success": false,
  "message": "Token no proporcionado."
}
```

### Escenario positivo — Con token

```
GET http://localhost/jwt-api/index.php
Authorization: Bearer <token>
```
Respuesta esperada: `200 OK` con el listado de productos.

## Autor

Shirley Wen — Grupo 1GS133
Desarrollo de Software VII — I Semestre 2026
Instructor: Ing. Irina Fong
