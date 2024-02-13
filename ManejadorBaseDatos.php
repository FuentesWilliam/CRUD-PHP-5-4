class ManejadorBaseDatos {

    private $conexion;
    private $lastQuery = null;

    private $host = 'xxx.xxx.xxx.xxx';
    private $user = 'xxxxx';
    private $password = 'xxxxx';
    private $database = 'xxxxx';


    public function __construct() {
        try {
            $this->conexion = new mysqli($this->host, $this->user, $this->password, $this->database);

            if ($this->conexion->connect_error) {
                throw new Exception('No se pudo conectar: ' . $this->conexion->connect_error);
            }
            
        }catch (Exception $e) {
            die(json_encode(['error' => $e->getMessage(), 'code' => $this->conexion->errno, 'line' => $e->getLine()]));
        }
    }

    /**
     * Consulta registros en la base de datos.
     * @param string $query La consulta SQL.
     * @param array $params Parámetros para la consulta preparada.
     * @return string JSON con los resultados o mensaje de error.
     * @throws Exception Si hay un error en la ejecución de la consulta.
     */
    public function consultarDatos($query, $params = [], $debug = false) {
        try {

            // Establecer $this->lastQuery solo si $debug es true
            if ($debug) {
                $this->lastQuery = $this->construirConsultaConValores($query, $params);
            }

            $stmt = $this->conexion->prepare($query); // Preparar la consulta

            if ($stmt === false) {
                // Manejar error en la preparación de la consulta
                $errorDetails = ($debug) ? ' - ' . $this->lastQuery : '';
                throw new Exception('Error en la preparación de la consulta: ' . $stmt->error . $errorDetails);
            }

            // Vincular parámetros si existen
            if (!empty($params)) {
                // Asume que todos los parámetros son strings por simplicidad
                $types = str_repeat("s", count($params));

                // Verificar si los tipos son válidos
                if (!in_array($types, ['s', 'i', 'd', 'b'])) {
                    throw new Exception('Tipos de parámetros no válidos: ' . $types);
                }

                // Asegurarse de que $types sea un array con un único elemento que contiene la cadena de tipos
                $bindParams = array_merge([$types], is_array($params) ? $params : [$params]);

                // Necesitas referenciar cada elemento de $bindParams para bind_param
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }

                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }

            $stmt->execute(); // Ejecutar la consulta
            $result = $stmt->result_metadata();

            if ($result === false) {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }

            // Construir array asociativo para almacenar resultados
            $parameters = [];
            $row = [];
            while ($field = $result->fetch_field()) {
                $row[$field->name] = null;
                $parameters[] = &$row[$field->name];
            }

            call_user_func_array([$stmt, 'bind_result'], $parameters); // Vincular resultados

            // Construir array de registros
            $data = [];
            while ($stmt->fetch()) {
                $registro = [];
                foreach ($row as $key => $val) {
                    // Aplicar el escape de caracteres especiales para seguridad XSS
                    $registro[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
                    // También puedes usar $registro[$key] = $val; sin seguridad XSS
                }
                $data[] = $registro;
            }

            if($debug){
                // Devolver información sobre el resultado
                $registros =[
                    'params' => $params,
                    'query_executed' => $queryWithValues
                ];
            }

            if (!empty($data)) {
                // Si hay datos, construir el array de resultados exitosos
                $registros = [
                    'success' => true,
                    'records' => $data
                ];
            } else {
                // Si no hay datos, construir el array de resultados fallidos
                $registros = [
                    'success' => false,
                    'message' => 'sin datos',
                ];
            }
 
            $stmt->close(); // Cerrar la sentencia preparada

            return $registros;

        } catch (Exception $e) {
            // Devolver información sobre el error
            return [
                'success' => false, 
                'message' => 'Error durante la consulta: ' . $e->getMessage() . ($debug ? ' - ' . $this->lastQuery : '')
            ];
        }
    }

    /**
     * insertar registros en la base de datos.
     * @param string $query La consulta SQL.
     * @param array $params Parámetros para la consulta preparada.
     * @return string JSON con los resultados o mensaje de error.
     * @throws Exception Si hay un error en la ejecución de la consulta.
     */
    public function insertarDatos($query, $params = [], $debug = false) {
        try{
            // Establecer $this->lastQuery solo si $debug es true
            if ($debug) {
                $this->lastQuery = $this->construirConsultaConValores($query, $params);
            }

            $stmt = $this->conexion->prepare($query); // Preparar la consulta

            if ($stmt === false) {
                // Manejar error en la preparación de la consulta
                $errorDetails = ($debug) ? ' - ' . $this->lastQuery : '';
                throw new Exception('Error en la preparación de la consulta: ' . $this->conexion->error . $errorDetails);
            }

            // Vincular parámetros si existen
            if (!empty($params)) {
                // Asume que todos los parámetros son strings por simplicidad
                $types = str_repeat("s", count($params));

                // Verificar si los tipos son válidos
                if (!in_array($types, ['s', 'i', 'd', 'b'])) {
                    throw new Exception('Tipos de parámetros no válidos: ' . $types);
                }

                // Asegurarse de que $types sea un array con un único elemento que contiene la cadena de tipos
                $bindParams = array_merge([$types], is_array($params) ? $params : [$params]);

                // Necesitas referenciar cada elemento de $bindParams para bind_param
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }

                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }

            $registrosAfectados = $stmt->execute(); // Ejecutar la consulta
            
            // Devolver información sobre el resultado solo si $debug es true
            $resultado = ($debug) ? ['params' => $params, 'query_executed' => $this->lastQuery] : [];

            if ($registrosAfectados > 0) {
                $resultado['success'] = true;
                $resultado['message'] = 'Inserción exitosa.';
                $resultado['records_affected'] = $registrosAfectados;
                $resultado['ultimo_id'] = $stmt->insert_id;
            } else {
                $resultado['success'] = false;
                $resultado['message'] = 'No se realizaron inserciones.';
                $resultado['records_affected'] = 0;
            }

            // Cerrar la sentencia preparada
            $stmt->close();
            return $resultado;

        } catch (Exception $e) {
            // Devolver información sobre el error
            return [
                'success' => false, 
                'message' => 'Error durante la consulta: ' . $e->getMessage() . ($debug ? ' - ' . $this->lastQuery : '')
            ];
        }
    }

    /**
     * Edita un registro en la base de datos.
     * @param string $query La consulta SQL de edición.
     * @param array $params Parámetros para la consulta preparada.
     * @return string JSON con el resultado de la operación o mensaje de error.
     * @throws Exception Si hay un error en la ejecución de la consulta.
     */
    public function actualizarDatos($query, $params = [], $debug = false) {
        try{
            // Establecer $this->lastQuery solo si $debug es true
            if ($debug) {
                $this->lastQuery = $this->construirConsultaConValores($query, $params);
            }

            $stmt = $this->conexion->prepare($query); // Preparar la consulta

            if ($stmt === false) {
                // Manejar error en la preparación de la consulta
                $errorDetails = ($debug) ? ' - ' . $this->lastQuery : '';
                throw new Exception('Error en la preparación de la consulta: ' . $this->conexion->error . $errorDetails);
            }

            // Vincular parámetros si existen
            if (!empty($params)) {
                // Asume que todos los parámetros son strings por simplicidad
                $types = str_repeat("s", count($params));

                // Verificar si los tipos son válidos
                if (!in_array($types, ['s', 'i', 'd', 'b'])) {
                    throw new Exception('Tipos de parámetros no válidos: ' . $types);
                }

                // Asegurarse de que $types sea un array con un único elemento que contiene la cadena de tipos
                $bindParams = array_merge([$types], is_array($params) ? $params : [$params]);

                // Necesitas referenciar cada elemento de $bindParams para bind_param
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }

                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }

            $registrosAfectados = $stmt->execute(); // Ejecutar la consulta

            // Devolver información sobre el resultado solo si $debug es true
            $resultado = ($debug) ? ['params' => $params, 'query_executed' => $this->lastQuery] : [];

            if ($registrosAfectados > 0) {
                $resultado['success'] = true;
                $resultado['message'] = 'Actualización exitosa.';
                $resultado['records_affected'] = $registrosAfectados;
            } else {
                $resultado['success'] = false;
                $resultado['message'] = 'No se realizaron actualizaciones.';
                $resultado['records_affected'] = 0;
            }

            // Cerrar la sentencia preparada
            $stmt->close();

            return $registros;

        } catch (Exception $e) {
            // Devolver información sobre el error
            return [
                'success' => false, 
                'message' => 'Error durante la consulta: ' . $e->getMessage() . ($debug ? ' - ' . $this->lastQuery : '')
            ];
        }
    }

    /**
     * Elimina registros de la base de datos.
     * @param string $query La consulta SQL de eliminación.
     * @param array $params Parámetros para la consulta preparada.
     * @param bool $debug Indica si se debe habilitar el modo de depuración.
     * @return array Array con el resultado de la operación o mensaje de error.
     * @throws Exception Si hay un error en la ejecución de la consulta.
     */
    public function eliminarDatos($query, $params = [], $debug = false) {
        try{
            // Establecer $this->lastQuery solo si $debug es true
            if ($debug) {
                $this->lastQuery = $this->construirConsultaConValores($query, $params);
            }

            $stmt = $this->conexion->prepare($query); // Preparar la consulta

            if ($stmt === false) {
                // Manejar error en la preparación de la consulta
                $errorDetails = ($debug) ? ' - ' . $this->lastQuery : '';
                throw new Exception('Error en la preparación de la consulta: ' . $this->conexion->error . $errorDetails);
            }

            // Vincular parámetros si existen
            if (!empty($params)) {
                // Asume que todos los parámetros son strings por simplicidad
                $types = str_repeat("s", count($params));

                // Verificar si los tipos son válidos
                if (!in_array($types, ['s', 'i', 'd', 'b'])) {
                    throw new Exception('Tipos de parámetros no válidos: ' . $types);
                }

                // Asegurarse de que $types sea un array con un único elemento que contiene la cadena de tipos
                $bindParams = array_merge([$types], is_array($params) ? $params : [$params]);

                // Necesitas referenciar cada elemento de $bindParams para bind_param
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }

                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }

            $stmt->execute(); // Ejecutar la consulta

            // Devolver información sobre el resultado solo si $debug es true
            $resultado = ($debug) ? ['params' => $params, 'query_executed' => $this->lastQuery] : [];

            if ($registrosAfectados > 0) {
                $resultado['success'] = true;
                $resultado['message'] = 'Eliminación exitosa.';
                $resultado['records_affected'] = $registrosAfectados;
            } else {
                $resultado['success'] = false;
                $resultado['message'] = 'No se realizaron eliminaciones.';
                $resultado['records_affected'] = 0;
            }

            // Cerrar la sentencia preparada
            $stmt->close();
            return $resultado;

        } catch (Exception $e) {
            // Devolver información sobre el error
            return [
                'success' => false, 
                'message' => 'Error durante la consulta: ' . $e->getMessage() . ($debug ? ' - ' . $this->lastQuery : '')
            ];
        }
    }

    private function construirConsultaConValores($query, $params) {
        // Verificar si $params es un array antes de iterar sobre él
        if (!is_array($params)) {
            return $query;
        }

        // Utilizar consultas preparadas con bind_param para mayor seguridad y rendimiento
        $stmt = $this->conexion->prepare($query);

        if ($stmt === false) {
            // Manejar el error de preparación de consulta
            return $query;
        }

        // Construir el string de tipos para bind_param
        $types = str_repeat("s", count($params));

        // Crear un array de referencias para los parámetros
        $bindParams = array($types);
        foreach ($params as &$param) {
            $bindParams[] = &$param;
        }

        // Llamar a bind_param con call_user_func_array
        call_user_func_array(array($stmt, 'bind_param'), $bindParams);

        // Devolver la consulta preparada
        return $stmt;
    }

    /**
     * Destructor de la clase que cierra la conexión a la base de datos.
     */
    public function __destruct() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
