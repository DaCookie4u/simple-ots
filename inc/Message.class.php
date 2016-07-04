<?php
defined('APP') or exit(0);

class Message {
  private $dbi = NULL;
  private $dbId = NULL;

  public function __construct($dbi) {
    $this->dbi = $dbi;

    $sql = '
      CREATE TABLE IF NOT EXISTS ots_table (
        `id` char(13) NOT NULL,
        `hash` char(60) NOT NULL,
        `data` blob NOT NULL,
        `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `iv` binary(16) NOT NULL,
        PRIMARY KEY (`id`)
      );
    ';

    $this->dbi->query($sql);
  }

  public function __destruct() {
    $this->dbi = null;
  }

  // create a new messages, returns:
  //  1: id from the database table
  //  2: key for decryption in hex
  public function CreateMessage($msg) {
    // creating the message-specific $key
    // when playing around with the size keep the database field-size in mind
    $key_size = 32; // 256 bits
    $key = openssl_random_pseudo_bytes($key_size, $strong);

    // creating the message-specific $iv
    // when playing around with the size keep the database field-size in mind
    $iv_size = 16; // 128 bits
    $iv = openssl_random_pseudo_bytes($iv_size, $strong);

    $this->dbId = uniqid();

    // insert the hashed $key and the encrypted $data inside the table
    // the dbquery() will return the insert id
    $sql = 'INSERT INTO ots_table (id, hash, data, iv) VALUES (:id, :hash, :data, :iv);';
    $this->dbi->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    $stmt = $this->dbi->prepare($sql);
    $stmt->bindParam(':id', $this->dbId);
    $stmt->bindParam(':hash', $this->Hash($key));
    $stmt->bindParam(':data', $this->Encrypt($msg, $key, $iv));
    $stmt->bindParam(':iv', $iv);
    $stmt->execute();
    $stmt = null;

    return array($this->dbId, bin2hex($key));
  }

  public function LoadMessage($id, $key) {
    // converting the $key to binary
    $key = pack("H*", $key);

    // setting the database id
    $this->dbId = $id;

    // getting the $iv from database
    $sql = 'SELECT hash, data, iv FROM ots_table WHERE id = :id LIMIT 0,1;';
    $stmt = $this->dbi->prepare($sql);
    $stmt->bindParam(':id', $this->dbId);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      if ($this->Compare(crypt($key, $row['hash']), $row['hash'])) {
      	$sql = 'DELETE FROM ots_table WHERE id = :id;';
        $stmt = $this->dbi->prepare($sql);
      	$stmt->bindParam(':id', $this->dbId);
      	$stmt->execute();

        $key = $key;
        $iv = $row['iv'];
      	$encMsg = $row['data'];

        $stmt = null;

        return $this->Decrypt($encMsg, $key, $iv);
      }
    }

    return false;
  }

  private function Encrypt($data, $key, $iv) {
    $enc_data = openssl_encrypt(
      $data,         // Without using OPENSSL_ZERO_PADDING, we will automatically get PKCS#7 padding
      'AES-256-CBC', // cipher and mode
      $key,          // secret key
      null,          // options (not used)
      $iv            // initialisation vector
    );

    return $enc_data;
  }

  private function Decrypt($enc_data, $key, $iv) {
    $data = openssl_decrypt(
      $enc_data,
      'AES-256-CBC',
      $key,
      null,
      $iv
    );

    return $data;
  }

  private function Hash($string) {
    $random = openssl_random_pseudo_bytes(18);
    $salt = sprintf('$2y$%02d$%s',
      13, // 2^n cost factor
      substr(strtr(base64_encode($random), '+', '.'), 0, 22)
    );

    $hash = crypt($string, $salt);

    return $hash;
  }

  private function Compare($givenhash, $dbhash) {
    $n1 = strlen($givenhash);
    if (strlen($dbhash) != $n1) {
      return false;
    }
    for ($i = 0, $diff = 0; $i != $n1; ++$i) {
      $diff |= ord($givenhash[$i]) ^ ord($dbhash[$i]);
    }
    return !$diff;
  }
}
?>
