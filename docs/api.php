<?php
namespace sodium;

/**
* @property-read string $pbin public key in binary form, 32 bytes
* @property-read string $phex public key in hexadecimal form, 64 bytes high-nibble first
*/
class public_key {

	function __construct();
	function __destruct();
	function __get($name);
	function __isset($name);

	/**
	* Clone copies standard properties and internal properties.
	* Clone does not copy dynamically added properties.
	* @return public_key
	*/
	function __clone();

	/**
	* Load a previously generated public_key string.
	*
	* @param string $public_key, Must be 32 bytes or 64 bytes hex. Originates from secret_key::pbin | secret_key::phex
	* @param bool $from_hex Specify whether the keys are in hexadecimal format, high-nibble first
	* @throws crypto_exception::code = crypto_exception::[ GENERAL | BAD_PUBLICKEY ]
	* @return public_key
	*/
	function load(string $public_key, bool $from_hex = true);
}

/**
* @property-read string $pbin public key in binary form, 32 bytes
* @property-read string $phex public key in hexadecimal form, 64 bytes high-nibble first
* @property-read string $sbin secret key in binary form, 32 bytes
* @property-read string $shex secret key in hexadecimal form, 64 bytes high-nibble first
*/
class secret_key {

	function __construct();
	function __destruct();
	function __get($name);
	function __isset($name);

	/**
	* Clone copies standard properties and internal properties.
	* Clone does not copy dynamically added properties.
	* @return secret_key
	*/
	function __clone();

	/**
	* Load a previously generated public_key & secret_key strings. Keys must have
	* been generated by crypto::keypair()
	*
	* @param string $public_key, Must be 32 bytes or 64 bytes hex. Originates from secret_key::pbin | secret_key::phex
	* @param string $secret_key, Must be 32 bytes or 64 bytes hex. Originates from secret_key::sbin | secret_key::shex
	* @param bool $from_hex Specify whether the keys are in hexadecimal format, high-nibble first
	* @throws crypto_exception::code = crypto_exception::[ GENERAL | BAD_PUBLICKEY | BAD_SECRETKEY ]
	* @return secret_key
	*/
	function load(string $public_key, string $secret_key, bool $from_hex = true);
}

/**
* @property-read string $cbin precomp key in binary form, 32 bytes
* @property-read string $chex precomp key in hexadecimal form, 64 bytes high-nibble first
*/
class precomp_key {

	function __construct();
	function __destruct();
	function __get($name);
	function __isset($name);

	/**
	* Clone copies standard properties and internal properties.
	* Clone does not copy dynamically added properties.
	* @return precomp_key
	*/
	function __clone();


	/**
	* Load a precomp_key. Using a precomp_key instead of a public_key/secret_key
	* with crypto::box, or crypto::box_open gains speed.
	*
	* Use a receiver's public key for $public_key and a sender's secret key for $secret_key
	* for crypto::box().
	*
	* Use a sender's public key for $public_key and a receiver's secret key for $secret_key
	* for crypto::box_open().
	*
	* @param public_key $public_key
	* @param secret_key $secret_key
	* @throws crypto_exception::code = crytpo_exception::[ GENERAL | BEFORENM_FAILED | LOAD_PUBLICKEY | LOAD_SECRETKEY ]
	* @return precomp_key
	*/
	function load(public_key $public_key, secret_key $secret_key);
}

class crypto {

	function __construct();
	function __destruct();

	/**
	* Generates a secret_key
	* @throws crypto_exception::code = crypto_exception::KEYPAIR_FAILED
	* @return secret_key
	*/
	function keypair();

	/**
	* Encrypts plain text using a nonce, a receiver's public key, and a sender's
	* secret key. The nonce must never be the same for all messages between
	* a sender -> receiver and receiver -> sender pair. Nonces can be reused between different
	* sender/receiver pairs. Use nonce::next() for the $nonce.
	* Use either public_key $receiver secret_key $sender | precomp_key $receiver.
	* crypto_box_beforenm() and crypto_box_afternm() functionality is implemented by using a precomp_key.
	* @param string $plain_text text to be encrypted
	* @param nonce $nonce
	* @param mixed $receiver { public_key | precomp_key }
	* @param mixed $sender { secret_key | null }
	* @throws crypto_exception::code = [ GENERAL | BAD_NONCE | LOAD_PUBLICKEY | LOAD_SECRET_KEY | LOAD_PRECOMPKEY | AFTERNM_BOX_FAILED ]
	* @return string binary data. Length is 16 bytes longer than $plain_text
	*/
	function box($plain_text, nonce $nonce, $receiver, $sender = null);

	/**
	* Decrypts encrypted data using a nonce, a sender's public key, and a receiver's
	* secret key. The nonce must never be the same for all messages between
	* a sender -> receiver and receiver -> sender pair. Nonces can be reused between different
	* sender/receiver pairs. Use nonce::set_next() for the $nonce.
	* Use either public_key $sender secret_key $receiver | precomp_key $sender.
	* crypto_box_beforenm() and crypto_box_open_afternm() functionality is implemented by using a precomp_key.
	* @param string $encrypted_text text to be decrypted
	* @param nonce $nonce
	* @param mixed $sender { public_key | precomp_key }
	* @param mixed $receiver { secret_key | null }
	* @throws crypto_exception::code = [ GENERAL | BAD_NONCE | LOAD_PUBLICKEY | LOAD_SECRET_KEY | LOAD_PRECOMPKEY | AFTERNM_BOX_OPEN_FAILED ]
	* @return string plain text. Length is 16 bytes less than $encrypted_text
	*/
	function box_open(string $encrypted_text, nonce $nonce, $sender, $receiver = null);

	/**
	* Generates random bytes
	* @param int $length
	* @throws crypto_exception::code = crypto_exception::GENERAL
	* @return string binary bytes of $length
	*/
	function random_bytes(int $length);
}

/**
* @property-read string $nbin nonce in binary form, 24 bytes
* @property-read string $nhex nonce in hexadecimal form, 48 bytes high-nibble first
*
*/
class nonce {

	function __construct();
	function __destruct();
	function __get($name);
	function __isset($name);

	/**
	* Clone copies standard properties and internal properties.
	* Clone does not copy dynamically added properties.
	* @return nonce
	*/
	function __clone();

	/**
	* Generates a new 24 byte nonce internally, or increments the current nonce.
	* The nonce is composed of 8 byte time stamp, 8 random bytes, and 8 byte counter.
	* The counter is initially set between 0 and 2^48. A new time stamp is choosen when
	* the counter rolls over.
	* @return nonce
	*/
	function next();

	/**
	* Sets an internal current nonce with a $new_nonce string when $affirm_greater is false.
	* No comparison is made between the current nonce and the $new_nonce.
	*
	* If $affirm_greater is true (default), the $new_nonce is compared to the current nonce.
	* The current nonce is replaced with the $new_nonce when the $new_nonce is greater than
	* the current nonce. A crypto_exception is throw when the $new_nonce is equal to or less than
	* the current nonce.
	*
	* A $new_nonce is greater when the first 16 bytes are greater. If the first 16 bytes are equal,
	* the last 8 byte counter is compared. The $new_nonce counter must also be greater.
	*
	* The comparison is big/little endian safe.
	*
	* @param string $new_nonce must be 24 bytes
	* @param bool $affirm_greater
	* @throws crypto_exception::code = [ GENERAL | BAD_NONCE ]
	* @return nonce
	*/
	function set_nonce(string $new_nonce, bool $affirm_greater = true);
}

class crypto_exception extends \Exception {

	const GENERAL = 0;
	const BAD_NONCE = 0;
	const LOAD_PUBLICKEY = 0;
	const LOAD_SECRETKEY = 0;
	const LOAD_PRECOMPKEY = 0
	const BAD_PUBLICKEY = 0;
	const BAD_SECRETKEY = 0;
	const KEYPAIR_FAILED = 0;
	const BOX_FAILED = 0;
	const BOX_OPEN_FAILED = 0;
	const BEFORENM_FAILED = 0;
	const AFTERNM_BOX_FAILED = 0;
	const AFTERNM_BOX_OPEN_FAILED = 0;
}
?>
