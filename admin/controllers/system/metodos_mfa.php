<?php
class MetodoMFAController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createMetodo($data)
    {
        try {
            $metodo = $data['tipo_metodo'];
            $estado = 0; // Active by default
            $sql = "INSERT INTO metodos_mfa (tipo_metodo, estado) VALUES (:metodo, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':metodo', $metodo);
            $stmt->bindParam(':estado', $estado);
            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Método añadido'];
            }
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateMetodo($data)
    {
        try {
            $id = $data['id'];
            $metodo = $data['tipo_metodo'];
            $sql = "UPDATE metodos_mfa SET tipo_metodo = :metodo WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':metodo', $metodo);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Método actualizado'];
            }
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function toggleEstado($id)
    {
        try {
            // First get current estado
            $sql = "SELECT estado FROM metodos_mfa WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $newEstado = $row['estado'] == 0 ? 1 : 0;
                
                $sql = "UPDATE metodos_mfa SET estado = :estado WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':estado', $newEstado);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $statusText = $newEstado == 0 ? 'activado' : 'desactivado';
                    return ['status' => true, 'message' => 'Método ' . $statusText];
                }
            }
            return ['status' => false, 'message' => 'Método no encontrado'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getMetodo($id)
    {
        try {
            $sql = "SELECT * FROM metodos_mfa WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllMetodos()
    {
        try {
            // Skip Token OTP (id=1) as per requirements
            $sql = "SELECT * FROM metodos_mfa";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estado = isset($row['estado']) ? $row['estado'] : 0;
                $estadoLabel = $estado == 0 ? 'Activo' : 'Inactivo';
                $toggleBtn = '<button class="btn ' . ($estado == 0 ? 'btn-danger' : 'btn-success') . ' btn-sm toggle" data-id="' . $row['id'] . '">' .
                            '<i class="fa-duotone fa-solid fa-' . ($estado == 0 ? 'ban' : 'check') . ' fa-lg"></i></button>';
                
                $data[] = [
                    'id' => $row['id'],
                    'tipo_metodo' => $row['tipo_metodo'],
                    'estado' => $estadoLabel,
                    'actions' => '<button class="btn btn-primary btn-sm edit" data-id="' . $row['id'] . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button> ' . $toggleBtn
                ];
            }
            return ['data' => $data];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
