<?php
function encryptData($data, $key) {
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData($data, $key) {
    $decodedData = base64_decode($data);
    if (strpos($decodedData, '::') === false) {
        throw new Exception('Invalid encrypted data format.');
    }
    list($encrypted_data, $iv) = explode('::', $decodedData, 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}
?>
