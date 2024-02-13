# ManejadorBaseDatos (PHP 5.4)

Este es un conjunto de funciones CRUD (Crear, Leer, Actualizar, Eliminar) para interactuar con una base de datos MySQL utilizando PHP 5.4.

## Clase `ManejadorBaseDatos`

### Método `__construct()`
- Constructor de la clase que establece la conexión a la base de datos.

### Método `consultarDatos($query, $params = [], $debug = false)`
- Consulta registros en la base de datos.
- **Parámetros:**
  - `$query`: La consulta SQL.
  - `$params`: Parámetros para la consulta preparada (opcional).
  - `$debug`: Indica si se debe habilitar el modo de depuración (opcional).
- **Retorno:**
  - Array con los resultados o mensaje de error.
  - Si `$debug` es `true`, incluye información adicional.

### Método `insertarDatos($query, $params = [], $debug = false)`
- Inserta registros en la base de datos.
- **Parámetros:**
  - `$query`: La consulta SQL.
  - `$params`: Parámetros para la consulta preparada (opcional).
  - `$debug`: Indica si se debe habilitar el modo de depuración (opcional).
- **Retorno:**
  - Array con los resultados o mensaje de error.
  - Si `$debug` es `true`, incluye información adicional.

### Método `actualizarDatos($query, $params = [], $debug = false)`
- Edita un registro en la base de datos.
- **Parámetros:**
  - `$query`: La consulta SQL de edición.
  - `$params`: Parámetros para la consulta preparada (opcional).
  - `$debug`: Indica si se debe habilitar el modo de depuración (opcional).
- **Retorno:**
  - Array con el resultado de la operación o mensaje de error.
  - Si `$debug` es `true`, incluye información adicional.

### Método `eliminarDatos($query, $params = [], $debug = false)`
- Elimina registros de la base de datos.
- **Parámetros:**
  - `$query`: La consulta SQL de eliminación.
  - `$params`: Parámetros para la consulta preparada (opcional).
  - `$debug`: Indica si se debe habilitar el modo de depuración (opcional).
- **Retorno:**
  - Array con el resultado de la operación o mensaje de error.
  - Si `$debug` es `true`, incluye información adicional.

### Método Privado `construirConsultaConValores($query, $params)`
- Construye la consulta final con valores reales.
- **Parámetros:**
  - `$query`: La consulta SQL.
  - `$params`: Parámetros para la consulta preparada.
- **Retorno:**
  - La consulta final con los valores reales.

---

**Optimizaciones para PHP 5.4:**
- Se han ajustado las funciones para ser compatibles con PHP 5.4.
- Se ha evitado el uso de características introducidas en versiones más recientes de PHP.

---

**Instrucciones de Uso:**
1. Descarga este archivo Markdown.
2. Utiliza estas funciones CRUD en tu proyecto PHP 5.4.
3. Personaliza según las necesidades de tu aplicación.
4. ¡No olvides ajustar la configuración de la conexión a la base de datos!

¡Espero que este archivo te sea útil para documentar y compartir tu código en Git! Si tienes más preguntas, ¡estaré encantado de ayudar!
