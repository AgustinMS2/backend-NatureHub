# NatureHub — Backend

NatureHub es una wiki de naturaleza donde usuarios registrados pueden crear artículos sobre especies, los cuales son revisados y aprobados por moderadores antes de ser publicados.

## Tecnologías

- **Backend:** PHP 8+
- **Base de datos:** MySQL 8+
- **Servidor web:** Apache (vía XAMPP)

---

## Requisitos previos

- [XAMPP](https://www.apachefriends.org/) instalado (incluye Apache, MySQL y PHP)
- El repositorio clonado dentro de `C:\xampp\htdocs\`

---

## Instalación y puesta en marcha

### 1. Ubicar el proyecto

Asegurate de que la carpeta del proyecto esté en:

```
C:\xampp\htdocs\backend-NatureHub\
```

### 2. Liberar el puerto de MySQL (si MySQL no arranca)

En Windows, el servicio de MySQL del sistema puede ocupar el puerto 3306 e impedir que el MySQL de XAMPP levante. Para liberarlo:

1. Abrí el **Administrador de tareas** (`Ctrl + Shift + Esc`).
2. Ir a la pestaña **Servicios**.
3. Buscá el servicio llamado **MySQL** (no el de XAMPP).
4. Hacé clic derecho → **Detener**.

También podés hacerlo desde PowerShell (como administrador):

```powershell
Stop-Service -Name MySQL -ErrorAction SilentlyContinue
```

### 3. Iniciar Apache y MySQL desde el XAMPP Control Panel

1. Abrí **XAMPP Control Panel** (buscalo en el menú Inicio o en `C:\xampp\xampp-control.exe`).
2. Hacé clic en **Start** junto a **Apache**.
3. Hacé clic en **Start** junto a **MySQL**.

Ambos deben quedar en verde. Si alguno falla, revisá que el puerto no esté ocupado (ver paso 2).

### 4. Importar la base de datos con phpMyAdmin

1. Abrí el navegador y entrá a `http://localhost/phpmyadmin`.
2. Hacé clic en la pestaña **Importar** (en la barra superior, sin seleccionar ninguna base de datos).
3. Seleccioná el archivo `naturehub.sql` ubicado en la carpeta `DB/` del proyecto.
4. Hacé clic en **Importar**.

Esto creará automáticamente la base de datos `naturehub` con todas las tablas y datos necesarios.

---

## Configuración de la conexión

El archivo `src/persistencia/ParametrosConexion.php` usa los valores por defecto de XAMPP:

| Parámetro     | Valor       |
|---------------|-------------|
| Servidor      | localhost   |
| Usuario       | root        |
| Contraseña    | (vacía)     |
| Base de datos | naturehub   |

Si tu configuración local es diferente, editá esos valores en ese archivo.

---

## Verificar que funciona

Con Apache y MySQL corriendo, la API estará disponible en:

```
http://localhost/backend-NatureHub/src/index.php/
```

Podés probar desde el navegador:

```
http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicaciones
```

Debería devolver un JSON con las publicaciones.
