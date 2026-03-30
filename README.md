# 🐳 Simple Docker Dashboard

[![PHP Version](https://img.shields.io/badge/php-8.4-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/docker-27.3.1-blue.svg)](https://docker.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Una interfaz web minimalista, liviana y **totalmente autocontenida** para gestionar tus contenedores Docker. Diseñada para ser rápida, segura y funcionar sin dependencias externas.

---

## ✨ Características

-   📊 **Estadísticas en Tiempo Real:** Visualiza consumo de CPU, Memoria, Red e I/O de disco con ciclo de actualización inteligente.
-   🖥️ **Resumen del Sistema:** Panel de control rápido con uso de CPU/RAM del host, estado de contenedores y tamaño total de imágenes.
-   📦 **Gestión de Contenedores:** Iniciar, detener, reiniciar, pausar/reanudar y configurar políticas de **Auto-reinicio**.
-   🐙 **Gestión de Docker Compose:** Visualiza, edita y despliega tus proyectos directamente desde un editor YAML integrado (`up -d` / `down`). Los proyectos se muestran **ordenados alfabéticamente** para una navegación más sencilla.
-   🔍 **Monitoreo y Auditoría:** Visualiza procesos activos (`top`) y cambios en el sistema de archivos (`diff`) en tiempo real.
-   🖼️ **Gestión de Imágenes:** Listado completo, historial de capas y herramientas de **Importación/Exportación** mediante archivos `.tar`.
-   🔗 **Repositorios GitHub:** Acceso directo al código fuente de las imágenes (detección automática o búsqueda).
-   🐚 **Terminal Integrada:** Acceso directo vía shell (`docker exec`) desde el navegador. Ahora incluye un modo de **Terminal del Host** para administrar el entorno del dashboard directamente.
-   🔐 **Seguridad:** Autenticación simple basada en sesiones y variables de entorno.
-   🎨 **Diseño Moderno:** Interfaz oscura basada en el tema Dracula y Bootstrap 5.3.
-   🌐 **Offline-Ready:** Todas las dependencias (Bootstrap, Iconos, Fuentes) están incluidas localmente.

## 🚀 Instalación Rápida

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/tu-usuario/SimpleDockerDashboard.git
    cd SimpleDockerDashboard
    ```

2.  **Configurar variables de entorno:**
    Crea un archivo `.env` en la raíz del proyecto:
    ```text
    DASHBOARD_USER=admin
    DASHBOARD_PASS=tu_password_seguro
    ```

3.  **Desplegar con Docker Compose:**
    ```bash
    docker compose up -d --build
    ```

4.  **Acceder:**
    Abre tu navegador en [http://localhost:6080](http://localhost:6080)

## 🏗️ Arquitectura

El proyecto está diseñado siguiendo principios de simplicidad y eficiencia:

-   **Backend:** PHP 8.4 corriendo sobre Apache. Se comunica con el host a través del socket `/var/run/docker.sock`.
-   **Frontend:** HTML5 plano, JavaScript (Fetch API) y Bootstrap 5.3 local.
-   **Infraestructura:** Un único contenedor que empaqueta las configuraciones de Apache y PHP para mayor portabilidad.
-   **Seguridad:** El usuario `www-data` dentro del contenedor pertenece al grupo `docker` del host (GID 126 por defecto), permitiendo comandos administrativos sin necesidad de `sudo`.

## 🛠️ Desarrollo y Personalización

-   **Archivos de la App:** Se encuentran en `www_data/`.
-   **Configuración Apache:** Localizada en `apache_data/` (incorporada en la imagen al construir).
-   **Logs:** Puedes ver los logs del dashboard con:
    ```bash
    docker logs -f SimpleDockerDashboard
    ```

## 🗺️ Hoja de Ruta (Próximas Mejoras)

- [ ] 💾 **Gestión de Volúmenes:** Ver y eliminar volúmenes huérfanos.
- [ ] 🌐 **Gestión de Redes:** Visualizar configuraciones de red e IPs.
- [ ] 🧹 **Mantenimiento:** Herramientas de `prune` para limpieza automática.

---

Hecho con ❤️ para la comunidad de Docker.
