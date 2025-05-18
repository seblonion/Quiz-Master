<?php
error_log("db.php : Début de l'initialisation de la connexion");

// Vérifier l'existence de config.php
$config_path = __DIR__ . '/config.php';
error_log("db.php : Tentative d'inclusion de config.php depuis $config_path");

if (!file_exists($config_path)) {
    error_log("db.php : Fichier $config_path introuvable");
    $db = null;
    return;
}

require_once $config_path;
error_log("db.php : config.php inclus");

// Vérifier les constantes de configuration
$required_constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$missing_constants = [];
foreach ($required_constants as $const) {
    if (!defined($const)) {
        $missing_constants[] = $const;
    }
}
if (!empty($missing_constants)) {
    error_log("db.php : Constantes manquantes dans config.php : " . implode(', ', $missing_constants));
    $db = null;
    return;
}
error_log("db.php : Constantes de configuration vérifiées : DB_HOST=" . DB_HOST . ", DB_NAME=" . DB_NAME . ", DB_USER=" . DB_USER);

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        error_log("db.php : Instance de Database créée avec host={$this->host}, dbname={$this->db_name}, user={$this->username}");
    }

    public function connect() {
        $this->conn = null;

        try {
            error_log("db.php : Tentative de connexion à la base de données {$this->db_name} sur {$this->host}");
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4',
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            error_log("db.php : Connexion à la base de données établie avec succès");
        } catch (PDOException $e) {
            error_log("db.php : Erreur de connexion à la base de données : " . $e->getMessage());
            $this->conn = null;
        }

        return $this->conn;
    }
}

// Instancier la classe et définir $db globalement
try {
    error_log("db.php : Instanciation de la classe Database");
    $database = new Database();
    $db = $database->connect();
    if ($db === null) {
        error_log("db.php : Échec de l'initialisation de \$db : la connexion a retourné null");
    } else {
        error_log("db.php : \$db initialisé avec succès comme objet PDO");
    }
} catch (Throwable $e) {
    error_log("db.php : Erreur inattendue lors de l'instanciation de Database : " . $e->getMessage());
    $db = null;
}
?>