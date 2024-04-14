# CodeLar

## Descripción del Proyecto
Implementa una API RESTful en PHP utilizando un framework como Laravel o Symfony que incluya un sistema de autenticación basado en JSON Web Tokens (JWT). La API debe proporcionar endpoints para el registro, inicio de sesión y acceso a recursos protegidos mediante la validación del token JWT.

### Tecnologías Utilizadas

- **PHP**: 8.3
- **NGINX**: 1.25-5.34.2
- **MariaDB**: 10.11-3.28.2
- **Docker & Docker Compose**

## Requisitos

- Docker
- Docker Compose
- Composer

## Instalación

### Paso 1: Configuración de Docker

Este proyecto emplea Docker4Drupal para simplificar la configuración y gestión de entornos de desarrollo. Asegúrate de tener Docker y Docker Compose instalados en tu sistema. Luego sigue estos pasos:

1. Clona el repositorio en tu máquina local:
   ```bash
   git clone https://github.com/omarguevara85/CodeLar
   cd CodeLar
   ```

2. Inicia los contenedores Docker usando Docker Compose:
   ```bash
   docker-compose up -d
   ```

Esto levantará los contenedores necesarios para el proyecto, como Drupal, la base de datos y otros servicios definidos en el docker-compose.yml.

### Paso 2: Instalación de Dependencias

Una vez los contenedores estén funcionando, accede al contenedor de PHP para instalar las dependencias de Composer:

```bash
docker exec -it [nombre-del-servicio-de-php] bash
```

Dentro del contenedor, ejecuta:

```bash 
composer install
```

### Paso 3: Instalación del Core de Drupal

Después de instalar las dependencias, procede con la instalación del core de Drupal. Esto puede hacerse a través de la interfaz web accediendo a tu localhost en el navegador, o usando el comando de Drush desde la línea de comandos:

```bash 
drush site-install --db-url=mysql://[usuario]:[password]@mariadb/[nombre-de-la-base-de-datos]
```

### Paso 4: Acceso y Uso

Tras la instalación, puedes acceder a la API navegando a http://localhost:[puerto-configurado] en tu navegador. Para más detalles sobre cómo utilizar y configurar tu API, puedes consultar la documentación oficial de los frameworks y librerías empleadas.