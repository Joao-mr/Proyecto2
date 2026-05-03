# Laravel 12 + Vue 3 API Base Project

Este proyecto es una base sólida diseñada para estudiantes y desarrolladores que deseen aprender a construir aplicaciones SPA (Single Page Application) modernas utilizando Laravel como API backend y Vue 3 como frontend.

## 🚀 Características Principales

### Backend (Laravel 12)
- **API RESTful**: Estructura robusta para servir datos al frontend.
- **Autenticación Sanctum**: Sistema seguro de autenticación basado en cookies/tokens.
- **Roles y Permisos**: Implementación de `spatie/laravel-permission` para gestión granular de accesos.
- **Recursos API**: Uso de API Resources para transformar datos de manera consistente.

### Frontend (Vue 3)
- **Composition API**: Uso moderno de Vue 3 con `<script setup>`.
- **Pinia**: Gestión de estado modular y persistente.
- **Vue Router**: Enrutamiento dinámico con protecciones de navegación (Guards).
- **PrimeVue**: Suite de componentes UI profesional y personalizable.
- **Tailwind CSS**: Estilizado utilitario para un diseño rápido y responsivo.
- **i18n**: Soporte multi-idioma (Español, Inglés, Francés, etc.).
- **Validación**: Formularios robustos con `yup`

## 🛠️ Requisitos Previos

- PHP >= 8.2
- Composer
- Node.js >= 16
- MySQL / MariaDB

## ⚙️ Instalación y Configuración

Sigue estos pasos para levantar el proyecto en tu entorno local:

Objetivo de instalacion: este repositorio debe poder instalarse desde un clon limpio usando Composer, npm, `.env`, `php artisan migrate --seed` y `npm run build`, sin limpieza manual de base de datos.

### 1. Clonar el Repositorio
```bash
git clone <url-del-repositorio>
cd Laravel-VUE-API-Base-Clase
```

### 2. Configurar Backend (Laravel)

Instalar dependencias de PHP:
```bash
composer install
```

Configurar variables de entorno:
```bash
cp .env.example .env
```

Generar clave de aplicación:
```bash
php artisan key:generate
```

Configurar base de datos en `.env`:
Abre el archivo `.env` y ajusta las credenciales de tu base de datos:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_bd
DB_USERNAME=root
DB_PASSWORD=
```

Configurar dominio para Sanctum (importante para autenticacion):
```dotenv
SANCTUM_STATEFUL_DOMAINS=localhost:8000
APP_URL=http://localhost:8000
```

Ejecutar migraciones y seeders:
```bash
php artisan migrate --seed
```
*Esto creará, categorías para un blog, usuarios, roles y permisos iniciales.*

### Credenciales de Acceso (Seeders)
Los siguientes usuarios son creaados por defecto:
- **Admin**: `admin@demo.com` / `12345678`
- **Usuario**: `user@demo.com` / `12345678`

### 3. Configurar Frontend (Vue)

Instalar dependencias de Node:
```bash
npm install
```

### 4. Ejecutar la Aplicación

Necesitarás dos terminales:

Terminal 1 (Backend):
```bash
php artisan serve
```

Terminal 2 (Frontend):
```bash
npm run dev
```

Accede a la aplicación en: `http://localhost:8000`

## 🧪 Pruebas API `sala-categorias` (Thunder Client)

Base URL: `http://127.0.0.1:8000/api`

1. **Listar relaciones**
	- Método: `GET`
	- URL: `{{baseUrl}}/sala-categorias`
	- Esperado: `200 OK`

2. **Crear relación sala-categoría**
	- Método: `POST`
	- URL: `{{baseUrl}}/sala-categorias`
	- Body JSON:
	```json
	{
	  "id_sala": 1,
	  "id_categoria": 1
	}
	```
	- Esperado: `201 Created`

3. **Consultar una relación específica (clave compuesta)**
	- Método: `GET`
	- URL: `{{baseUrl}}/sala-categorias/1/1`
	- Esperado: `200 OK`

4. **Actualizar relación (cambiar clave compuesta)**
	- Método: `PUT`
	- URL: `{{baseUrl}}/sala-categorias/1/1`
	- Body JSON:
	```json
	{
	  "id_sala": 2,
	  "id_categoria": 2
	}
	```
	- Esperado: `200 OK`

5. **Eliminar relación**
	- Método: `DELETE`
	- URL: `{{baseUrl}}/sala-categorias/2/2`
	- Esperado: `204 No Content`

Notas rápidas:
- Si intentas registrar una relación duplicada, el API responde `422`.
- Asegúrate de que existan previamente registros en `salas` y `categorias`.
- Puedes validar también con test automatizado: `vendor/bin/phpunit --filter SalaCategoriaApiTest`.

## 📂 Estructura del Proyecto

### Backend (`app/`)
- `Http/Controllers/Api`: Controladores que manejan las peticiones API.
- `Http/Resources`: Transformadores de datos JSON.
- `Models`: Modelos Eloquent.

### Frontend (`resources/js/`)
- `components`: Componentes Vue reutilizables (Botones, Inputs, etc.).
- `composables`: Lógica reutilizable (Hooks) para API, validación, etc.
- `layouts`: Plantillas principales (Admin, User, Guest).
- `pages` / `views`: Vistas de la aplicación organizadas por módulos.
- `store`: Estados globales con Pinia (Auth, Lang, etc.).
- `routes`: Definición de rutas y guards.
