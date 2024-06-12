<?php

class Encryption {
    private $key;

    public function __construct($key) {
        $this->key = $key;
    }

    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));
        $tag = '';
        $encryptedData = openssl_encrypt($data, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $encryptedData);
    }

    public function decrypt($data) {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-gcm');
        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, 16);
        $encryptedData = substr($data, $ivLength + 16);
        return openssl_decrypt($encryptedData, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}
?>
