<?php
/**
 * Base Model Class
 * Provides shared database connection and CRUD helpers for model classes.
 */

require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    /** @var mysqli */
    protected $db;

    /** Table name for the model. */
    protected $table;

    /** Primary key column for the model. */
    protected $primaryKey;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Execute a prepared statement, returning a standard success/error array.
     */
    protected function resultFromExecute($stmt, $successMessage) {
        return $stmt->execute()
            ? ['success' => true, 'message' => $successMessage]
            : ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Execute an INSERT statement, returning the new id under $idKey on success.
     */
    protected function insertResult($stmt, $idKey, $successMessage) {
        if ($stmt->execute()) {
            return ['success' => true, $idKey => $this->db->insert_id, 'message' => $successMessage];
        }
        return ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Build and execute a dynamic UPDATE from a whitelist of fields.
     *
     * @param mixed  $id            Primary key value of the row to update.
     * @param array  $data          Incoming data keyed by column name.
     * @param array  $allowedFields Columns that may be updated from $data.
     * @param array  $fieldTypes    Optional map of column => mysqli type char (defaults to 's').
     * @param array  $extra         Extra computed columns, each ['column' =>, 'type' =>, 'value' =>].
     */
    protected function updateRecord($id, array $data, array $allowedFields, array $fieldTypes = [], array $extra = [], $successMessage = 'Record updated successfully') {
        $updateFields = [];
        $types = '';
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $types .= $fieldTypes[$field] ?? 's';
                $values[] = $data[$field];
            }
        }

        foreach ($extra as $col) {
            $updateFields[] = "{$col['column']} = ?";
            $types .= $col['type'];
            $values[] = $col['value'];
        }

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $types .= 'i';
        $values[] = $id;

        $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param($types, ...$values);
        return $this->resultFromExecute($stmt, $successMessage);
    }

    /**
     * Hard-delete a row by primary key.
     */
    protected function deleteRecord($id, $successMessage) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id);
        return $this->resultFromExecute($stmt, $successMessage);
    }

    /**
     * Check whether a row exists where $column = $value, optionally excluding one id.
     */
    protected function existsWhere($column, $value, $excludeId = null) {
        $query = "SELECT {$this->primaryKey} FROM {$this->table} WHERE $column = ?";
        if ($excludeId !== null) {
            $query .= " AND {$this->primaryKey} != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $value, $excludeId);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $value);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Count rows, optionally filtered by a raw WHERE clause.
     */
    protected function countRecords($where = '1') {
        $result = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE $where");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}

?>
