# Información del Proyecto

Este documento describe el desarrollo y la estructura de nuestro sistema, una plataforma orientada a gestionar reservas de servicios con profesionales. El proyecto permite el registro de usuarios, la compra de paquetes de sesiones y la coordinación de agendas, facilitando además la comunicación directa mediante videollamadas.

### Organización del Proyecto

La aplicación está diseñada siguiendo una arquitectura separada. Por un lado, tenemos el backend construido enteramente en el framework Laravel, que se encarga de manejar toda la lógica de negocio, la seguridad y la persistencia de datos. Por otro lado, contamos con una aplicación de frontend independiente construida con Vue.js que se ubica en su propia carpeta. Esta aplicación cliente se comunica con el backend mediante una API REST, lo que nos permite ofrecer una interfaz de usuario fluida y reactiva. 

### Archivos y Carpetas Relevantes

Dentro de la estructura del backend, la carpeta principal es app, donde reside toda la lógica central de la aplicación. Los modelos que representan las tablas de la base de datos se ubican en la carpeta Models, destacando archivos como CompraPaquete o Usuario. Las peticiones entrantes son procesadas por los controladores en Http/Controllers. También es muy importante la carpeta Http/Requests, ya que allí validamos toda la información que envían los usuarios antes de procesarla, como en el caso de RegisterProfesionalRequest o StoreExcepcionAgendaRequest. 

Para las tareas pesadas que no deben detener la experiencia del usuario, utilizamos la carpeta Jobs, donde procesamos elementos en segundo plano como por ejemplo ProcesarPagoJob. Finalmente, todas las integraciones de la aplicación se administran desde la carpeta config.

### Herramientas Utilizadas

El núcleo del backend fue desarrollado utilizando Laravel bajo el lenguaje PHP, mientras que la interfaz web fue creada con Vue.js. Para el almacenamiento principal de información utilizamos una base de datos relacional. 

Adicionalmente, tuvimos que integrar varios servicios externos para cumplir con los requerimientos. Implementamos PayPal para procesar todos los pagos y la compra de paquetes de sesiones. Para el sistema de videollamadas en vivo entre profesionales y clientes nos apoyamos en la tecnología de LiveKit. Además, utilizamos Redis para el manejo de colas de procesos y autenticación mediante Google para facilitar el registro y el ingreso de los usuarios a la plataforma.

### Funcionalidades Principales

El sistema permite que los usuarios se registren bajo distintos roles, principalmente clientes y profesionales. Los profesionales pueden configurar sus días y horarios disponibles, y también agregar excepciones a su agenda si no pueden atender en ciertas fechas particulares. 

Por su parte, los clientes pueden explorar estas agendas y reservar horarios específicos o adquirir paquetes de sesiones completas abonando de forma segura con PayPal. Una vez que una reserva está confirmada, la plataforma proporciona un espacio de videollamada integrado para que la sesión se pueda llevar a cabo de forma virtual sin tener que salir de la página. Finalmente, el sistema cuenta con apartados administrativos para monitorear la plataforma y gestionar las reservas de forma global.
