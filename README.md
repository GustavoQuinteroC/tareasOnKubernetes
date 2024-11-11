
# Proyecto Lista de Tareas

Este proyecto es una aplicación web para gestionar una lista de tareas, que permite agregar, editar, eliminar y marcar tareas como completadas. El proyecto utiliza PHP, MySQL (MariaDB) y Apache, y se despliega en un clúster de Kubernetes utilizando Docker para la contenedorización de los servicios.

## Estructura del Proyecto

La aplicación consta de:

- **Frontend (HTML, Bootstrap, JavaScript)**: Interfaz de usuario donde se pueden agregar, editar y eliminar tareas, así como marcar tareas como completadas.
- **Backend (PHP, MariaDB)**: API RESTful que maneja las tareas (GET, POST, PUT, DELETE, PATCH) y se conecta a una base de datos MariaDB.
- **Base de datos (MariaDB)**: Guarda las tareas en una base de datos relacional.

### Archivos principales:

- **`index.html`**: Página principal con la interfaz de usuario.
- **`tasks.php`**: Archivo PHP que maneja la lógica de la API de tareas (CRUD).
- **`db.php`**: Conexión a la base de datos MariaDB.
  
## Requisitos

Antes de comenzar, asegúrate de tener instalados los siguientes componentes:

1. **Docker**: Para contenedores y gestión de imágenes.
2. **Kubernetes**: Para orquestar los contenedores.
3. **kubectl**: Herramienta de línea de comandos de Kubernetes.
4. **Minikube** (opcional): Si deseas ejecutar Kubernetes localmente.
  
## Instalación y Configuración

### 1. Configuración de Docker

Primero, crea las imágenes de Docker para la web y la base de datos.

#### Dockerfile para la web (Apache + PHP)

Este archivo es necesario para crear la imagen Docker del servicio web. Asegúrate de tener tu archivo HTML, `tasks.php` y `db.php` en el mismo directorio.

```dockerfile
# Imagen base de PHP con Apache
FROM php:8.3-apache

# Habilitar la extensión PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copiar los archivos del proyecto a la imagen Docker
COPY ./ /var/www/html/

# Configurar Apache para servir el contenido
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
```

#### Dockerfile para la base de datos (MariaDB)

Este es el archivo necesario para crear la imagen Docker de MariaDB. Usaremos la imagen oficial de MariaDB para este proyecto.

```dockerfile
# Usar imagen oficial de MariaDB
FROM mariadb:latest

# Configurar base de datos
ENV MYSQL_ROOT_PASSWORD=rootpassword
ENV MYSQL_DATABASE=tareas_db
```

#### Construir las imágenes

Para construir las imágenes Docker, ejecuta los siguientes comandos en el directorio donde están tus `Dockerfile`:

```bash
docker build -t tareas-web ./web/
docker build -t tareas-db ./db/
```

### 2. Configuración de Kubernetes

Para ejecutar el proyecto en Kubernetes, necesitarás crear los archivos de despliegue y servicio para los contenedores web y base de datos.

#### 2.1 Desplegar MariaDB en Kubernetes

Crea un archivo `mariadb-deployment.yaml` para el despliegue de MariaDB en Kubernetes:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      containers:
      - name: mariadb
        image: tareas-db:latest
        env:
        - name: MYSQL_ROOT_PASSWORD
          value: "rootpassword"
        - name: MYSQL_DATABASE
          value: "tareas_db"
        ports:
        - containerPort: 3306
      volumes:
        - name: mariadb-data
          persistentVolumeClaim:
            claimName: mariadb-pvc

---
apiVersion: v1
kind: Service
metadata:
  name: mariadb-service
spec:
  ports:
    - port: 3306
  selector:
    app: mariadb
```

#### 2.2 Desplegar el servicio web (Apache + PHP)

Ahora, crea el archivo `web-deployment.yaml` para el despliegue de la aplicación web en Kubernetes:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: web
spec:
  replicas: 1
  selector:
    matchLabels:
      app: web
  template:
    metadata:
      labels:
        app: web
    spec:
      containers:
      - name: web
        image: tareas-web:latest
        ports:
        - containerPort: 80
        env:
        - name: DB_HOST
          value: "mariadb-service"
        - name: DB_NAME
          value: "tareas_db"
        - name: DB_USER
          value: "root"
        - name: DB_PASSWORD
          value: "rootpassword"

---
apiVersion: v1
kind: Service
metadata:
  name: web-service
spec:
  selector:
    app: web
  ports:
    - port: 80
  type: LoadBalancer
```

#### 2.3 Crear Volumen Persistente para MariaDB

Para asegurar la persistencia de los datos de MariaDB, crea un archivo `mariadb-pvc.yaml`:

```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mariadb-pvc
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
```

### 3. Desplegar en Kubernetes

#### 3.1 Aplicar las configuraciones de Kubernetes

Aplica los archivos YAML con los siguientes comandos:

```bash
kubectl apply -f mariadb-pvc.yaml
kubectl apply -f mariadb-deployment.yaml
kubectl apply -f web-deployment.yaml
```

#### 3.2 Verificar el estado de los pods

Para verificar que todos los pods estén corriendo correctamente:

```bash
kubectl get pods
```

#### 3.3 Acceder a la aplicación

Para acceder a la aplicación, debes obtener la IP pública del servicio web si estás usando un clúster en la nube, o usar el servicio `kubectl port-forward` para un clúster local:

```bash
kubectl port-forward svc/web-service 8080:80
```

Accede a la aplicación en tu navegador: `http://localhost:8080`.

## Funcionalidades

- **Agregar tarea**: Agregar una nueva tarea con título, descripción y prioridad.
- **Editar tarea**: Cambiar el título, la descripción o la prioridad de una tarea existente.
- **Eliminar tarea**: Eliminar una tarea de la lista.
- **Marcar tarea como completada**: Cambiar el estado de la tarea de pendiente a completada.
  
## Archivos del Proyecto

### `index.html`

Este archivo contiene el código HTML y JavaScript para la interfaz de usuario. Utiliza Bootstrap para el diseño y hace uso de un API RESTful para interactuar con las tareas.

### `tasks.php`

Este archivo PHP contiene la lógica de negocio para interactuar con la base de datos (CRUD) y servir las tareas en formato JSON.

### `db.php`

Este archivo contiene la configuración de la conexión a la base de datos MariaDB utilizando PDO.

## Contribuciones

Si deseas contribuir a este proyecto, por favor realiza un fork del repositorio y envía un pull request con tus cambios.

## Licencia

Este proyecto está bajo la Licencia MIT.

---

¡Gracias por revisar el proyecto! Si tienes alguna pregunta o necesitas ayuda, no dudes en abrir un *issue* o hacer un pull request.
