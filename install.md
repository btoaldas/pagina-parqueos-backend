# Install

## 1. Descargar el Proyecto Como ZIP

![](_assets/1.download-project.png)

## 2. Descargar e installar Xampp

![](_assets/2.download-xampp-and-install.png)

## 3. Descargar e installar Composer

![](_assets/3.download-and-install-composer.png)

## 4. Copiar el proyecto en la carpeta `htdocs`

Path: `C:\xampp\htdocs`

![](_assets/4.move-zip.png)

## 5. Extraer el proyecto y ponerle un ejemplo

Ejemplo: `C:\xampp\htdocs\backend`

![](_assets/5.extract-here-and-changename.png)

## 6. Verificar el contenido de la carpeta

![](_assets/6.inside.png)

## 7. Copiar el archivo `.env.example` y nombrarlo `.env`

![](_assets/7.copy-env.png)

## 8. Configurar `.env`

PATH: poner el nombre del proyecto`/backend`

![](_assets/8.edit-env.png)

## 9. Crear una carpeta para las imagenes

La carpeta puede ser en cualquier lugar y con cualquier nombre.

Ejemplo: `C:\Users\%USERNAME%\Escritorio\imagenes`

![](_assets/9.new-folder.png)

## 10. Crear una carpeta `fine` dentro

![](_assets/10.create-fine.png)

## 11. Agregar la primera carpeta en `PATH_STORAGE`

Reemplazar los `\` por `/`.

Inicio: `C:\Users\%USERNAME%\Escritorio\imagenes`
<br/>
Fin: `C:/Users/%USERNAME%/Escritorio/imagenes`

![](_assets/11.copywithchange.png)

## 12. Iniciar Xampp con Apache y Mysql

![](_assets/12.start-xampp-apache-and-mysql.png)

## 13. Copiar el sql del archivo `README.md` del repositorio.

url: <a href="https://github.com/cristianmauricio612/pagina-parqueos-backend/blob/main/README.md">`https://github.com/cristianmauricio612/pagina-parqueos-backend/blob/main/README.md`</a>

![](_assets/13.copysql.png)

## 14. Crear la base de datos en phpmyadmin

<a href="http://localhost/phpmyadmin">PhpMyAdmin</a>

- El nombre de la base de datos tiene que ser el mismo que el de `.env`.

![](_assets/14.gotophpmyadmin.png)

## 15. Copiar SQL

Copiar el SQL y ejectuar todo el código en la base de datos.

![](_assets/15.paste.png)

## 16. Abrir terminal en el proyecto

Abrir la terminal en el proyecto backend.

![](_assets/16.openterminal.png)

## 17. Iniciar el comando de composer

Comando: `composer install`

![](_assets/17.startcommand.png)

## 18. Agregar Roles en la base de datos.

![](_assets/18.roles.png)

## 19. Verficación rápida

- Copiar una imagen en la caperta de images dentro de la carpeta fine. Ejemplo `test.jpg`
- Buscar la imagen en el navegador
- Ejemplo: http://localhost/backend/api/v1/storage/fine/test.jpg

![](_assets/19.verify.png)
