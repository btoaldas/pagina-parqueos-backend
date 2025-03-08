# CONFIGURACIÓN DE BACKEND

Este proyecto es una aplicación para gestionar un sistema de estacionamiento rotativo. A continuación, se detallan los pasos para configurar el entorno de desarrollo en Arch Linux y Windows.

---

## Requisitos Previos

1. PHP: Asegúrate de tener PHP instalado.

2. Apache (httpd): Servidor web para hostear la aplicación.

3. MySQL: Base de datos para almacenar la información.

4. Composer: Gestor de dependencias para PHP (opcional, si usas bibliotecas externas).

---

## Configuración del Entorno

### 1. Configuración de PHP

#### Arch Linux

1. Abre el archivo de configuración de PHP:

```bash
sudo nano /etc/php/php.ini
```

2. Habilita las siguientes extensiones (elimina el `;` al inicio de la lí­nea):

```ini
extension=mysqli
extension=pdo_mysql
```

3. Guarda y cierra el archivo (`Ctrl + O`, `Enter`, `Ctrl + X`).

4. Reinicia Apache para aplicar los cambios:

```bash
sudo systemctl restart httpd
```

#### Windows (XAMPP)

1. Abre el archivo `php.ini` (ubicado en `C:\xampp\php\php.ini`).

2. Habilita las siguientes extensiones (elimina el `;` al inicio de la lí­nea):

```ini
extension=mysqli
extension=pdo_mysql
```

3. Guarda y cierra el archivo.

4. Reinicia Apache desde el panel de control de XAMPP.

### 2. Configuración de Apache (httpd)

#### Arch Linux

1. Abre el archivo de configuración de Apache:

```bash
sudo nano /etc/httpd/conf/httpd.conf
```

2. Asegúrate de que el módulo `rewrite` está habilitado. Busca la siguiente línea y descomántala si es necesario:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

3. Configura `AllowOverride` para permitir el uso de `.htaccess`. Busca la sección correspondiente a tu directorio de proyecto y cambia `AllowOverride None` a `AllowOverride All`:

```apache
<Directory "/var/www/html">
  Options Indexes FollowSymLinks
  AllowOverride All
  Require all granted
</Directory>
```

4. Guarda y cierra el archivo.

5. Reinicia Apache:

```bash
sudo systemctl restart httpd
```

#### Windows (XAMPP)

1. Abre el archivo `httpd.conf` (ubicado en `C:\xampp\apache\conf\httpd.conf`).

2. Asegúrate de que el módulo rewrite está habilitado. Busca la siguiente lÃínea y descoméntala si es necesario:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

3. Configura `AllowOverride` para permitir el uso de `.htaccess`. Busca la sección correspondiente a tu directorio de proyecto y cambia `AllowOverride None` a `AllowOverride All`:

```apache
<Directory "C:/xampp/htdocs">
  Options Indexes FollowSymLinks
  AllowOverride All
  Require all granted
</Directory>
```

4. Guarda y cierra el archivo.

5. Reinicia Apache desde el panel de control de XAMPP.

## 3. Configuración de MySQL

### 1. Crear la Base de Datos:

Ejecuta el script SQL proporcionado para crear las tablas y la estructura de la base de datos.

Puedes hacerlo desde la lÃ­nea de comandos o usando una herramienta como phpMyAdmin o MySQL Workbench.

```sql
DROP TABLE IF EXISTS multas;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS espacios;
DROP TABLE IF EXISTS zonas;
DROP TABLE IF EXISTS vehiculos;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;


CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    cdigo_recuperacion VARCHAR(64) NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

CREATE TABLE vehiculos (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    placa VARCHAR(20) NOT NULL UNIQUE,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    año INT NOT NULL,
    base_imponible DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE zonas (
    id_zona INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tarifa DECIMAL(10, 2) NOT NULL,
    tiempo_maximo INT NOT NULL
);

CREATE TABLE espacios (
    id_espacio INT AUTO_INCREMENT PRIMARY KEY,
    id_zona INT NOT NULL,
    estado VARCHAR(50) NOT NULL, -- Ejemplo: "disponible", "ocupado", "mantenimiento"
    tipo VARCHAR(50) NOT NULL,   -- Ejemplo: "automÃ³vil", "motocicleta", "discapacitado"
    FOREIGN KEY (id_zona) REFERENCES zonas(id_zona)
);

CREATE TABLE tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    placa VARCHAR(20) NOT NULL,
    fecha_entrada DATETIME NOT NULL,
    fecha_salida DATETIME,
    monto DECIMAL(10, 2),
    estado VARCHAR(50) NOT NULL, -- Ejemplo: "activo", "finalizado", "cancelado"
    id_espacio INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_espacio) REFERENCES espacios(id_espacio)
);

CREATE TABLE multas (
    id_multa INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    descripcion VARCHAR(255),
    evidencia VARCHAR(255), -- Ruta o URL de la evidencia (foto, video, etc.)
    estado VARCHAR(50) NOT NULL, -- Ejemplo: "pendiente", "pagada", "cancelada"
    fecha_pago DATETIME NULL,
    FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket)
);

ALTER TABLE roles AUTO_INCREMENT = 1;
ALTER TABLE usuarios AUTO_INCREMENT = 1;
ALTER TABLE zonas AUTO_INCREMENT = 1;
ALTER TABLE espacios AUTO_INCREMENT = 1;
ALTER TABLE tickets AUTO_INCREMENT = 1;
ALTER TABLE multas AUTO_INCREMENT = 1;

INSERT INTO roles (nombre_rol, descripcion)
VALUES
    ('cliente', 'cliente descripcion'),
    ('admin', 'admin descripcion'),
    ('empleado', 'empleado descripcion');

INSERT INTO zonas (nombre, tarifa, tiempo_maximo)
VALUES
    ('Zona Roja', '12.5', 12000),
    ('Zona Azul', '10.5', 10000),
    ('Zona Verde', '15.5', 8000),
    ('Zona Morada', '8.0', 2400);

INSERT INTO espacios (id_zona, estado, tipo)
VALUES
    (1, "disponible", "discapacitado"),
    (1, "disponible", "automovil"),
    (1, "disponible", "automovil"),
    (1, "disponible", "motocicleta"),
    (2, "disponible", "automovil"),
    (2, "disponible", "automovil"),
    (2, "disponible", "automovil"),
    (3, "disponible", "automovil"),
    (3, "disponible", "motocicleta"),
    (3, "disponible", "automovil"),
    (3, "disponible", "discapacitado");
```

### 2. Configurar el Acceso:

Asegúrate de que el usuario de MySQL tenga permisos para acceder a la base de datos.

## 4. Configuración del Archivo `.env`

### 1. Crea un archivo .env en la raí­z del proyecto con el siguiente contenido:

```ini
# Ruta base del proyecto (si está en un subdirectorio)

PATH_BASE=/ruta/al/proyecto

# Configuración de la base de datos

DB_HOST=127.0.0.1 # Usar 127.0.0.1 en lugar de localhost si hay problemas de conexión
DB_NAME=estacionamiento
DB_USER=root
DB_PASS=secret

# Configuración de JWT

JWT_SECRET=tu_clave_secreta_jwt
```

#### Explicación de las Variables:

- `PATH_BASE`: Ruta base del proyecto si está en un subdirectorio. Por ejemplo, si el proyecto está en http://localhost/mi-proyecto, entonces `PATH_BASE=/mi-proyecto`.

- DB_HOST: Dirección del servidor de la base de datos. Usa `127.0.0.1` si localhost no funciona.

- DB_NAME: Nombre de la base de datos.

- DB_USER: Usuario de la base de datos.

- DB_PASS: Contraseña del usuario de la base de datos.

- JWT_SECRET: Clave secreta para firmar los tokens JWT.

## 5. Ejecutar el Proyecto

### 1. Clona el Repositorio:

```bash
git clone https://github.com/cristianmauricio612/pagina-parqueos-backend.git
cd pagina-parqueos-backend
```

### 2. Configura el Archivo `.env`:

Crea el archivo `.env` como se explica anteriormente.

### 3. Inicia el Servidor:

En Arch Linux, asegúrate de que Apache está en ejecución:

```bash
sudo systemctl start httpd
```

En Windows, inicia Apache y MySQL desde el panel de control de XAMPP.

### 4. Accede al Proyecto:

Abre tu navegador y visita `http://localhost` (o `http://localhost/mi-proyecto` si usas `PATH_BASE`).

## 6. Ejecutar el Script SQL

### 1. Desde la Lí­nea de Comandos:

```bash
mysql -u root -p estacionamiento < database.sql
```

### 2. Desde phpMyAdmin o MySQL Workbench:

Abre la herramienta, selecciona la base de datos estacionamiento y ejecuta el código SQL.

---

## Notas Adicionales

- **Problemas con localhost**: En algunos sistemas, localhost no funciona correctamente para la conexión a MySQL. Usa `127.0.0.1` en su lugar.

- **Permisos de Archivos**: Asegúrate de que los archivos tengan los permisos adecuados para que Apache pueda leerlos.

- **Entornos de Producción**: En producción, asegúrate de deshabilitar la visualización de errores y proteger el archivo `.env`.
