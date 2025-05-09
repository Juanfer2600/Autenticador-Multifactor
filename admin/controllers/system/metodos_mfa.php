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
            $sql = "INSERT INTO metodos_mfa (tipo_metodo) VALUES (:metodo)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':metodo', $metodo);
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
            $sql = "SELECT * FROM metodos_mfa";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = [
                    'id' => $row['id'],
                    'tipo_metodo' => $row['tipo_metodo'],
                    'actions' => '<button class="btn btn-success btn-sm edit" data-id="' . $row['id'] . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>'
                ];
            }
            return ['data' => $data];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
