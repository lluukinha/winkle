<?php

$Aes = array();  // Aes namespace

/**
 * AES Cipher function: encrypt 'input' state with Rijndael algorithm
 *   applies Nr rounds (10/12/14) using key schedule w for 'add round key' stage
 *
 * @param {Number[]} input 16-byte (128-bit) input state array
 * @param {Number[][]} w   Key schedule as 2D byte-array (Nr+1 x Nb bytes)
 * @returns {Number[]}     Encrypted output state array
 */
$Aes['cipher'] = function($input, $w) {    // main Cipher function [§5.1]
   $Nb = 4;               // block size (in words): no of columns in state (fixed at 4 for AES)
   $Nr = $w['length']/$Nb - 1; // no of rounds: 10/12/14 for 128/192/256-bit keys

   $state = array(array(),array(),array(),array());  // initialise 4xNb byte-array 'state' with input [§3.4]
  for ( $i=0; $i<4*$Nb; $i++) $state[$i%4][Math['floor']($i/4)] = $input[$i];

  $state = $Aes['addRoundKey']($state, $w, 0, $Nb);

  for ( $round=1; $round<$Nr; $round++) {
    $state = $Aes['subBytes']($state, $Nb);
    $state = $Aes['shiftRows']($state, $Nb);
    $state = $Aes['mixColumns']($state, $Nb);
    $state = $Aes['addRoundKey']($state, $w, $round, $Nb);
  }

  $state = $Aes['subBytes']($state, $Nb);
  $state = $Aes['shiftRows']($state, $Nb);
  $state = $Aes['addRoundKey']($state, $w, $Nr, $Nb);

   $output = [4*$Nb];  // convert state to 1-d array before returning [§3.4]
  for ( $i=0; $i<4*$Nb; $i++) $output[$i] = $state[$i%4][Math['floor']($i/4)];
  return $output;
}

/**
 * Perform Key Expansion to generate a Key Schedule
 *
 * @param {Number[]} key Key as 16/24/32-byte array
 * @returns {Number[][]} Expanded key schedule as 2D byte-array (Nr+1 x Nb bytes)
 */
$Aes['keyExpansion'] = function($key) {  // generate Key Schedule (byte-array Nr+1 x Nb) from Key [§5.2]
   $Nb = 4;            // block size (in words): no of columns in state (fixed at 4 for AES)
   $Nk = $key['length']/4  // key length (in words): 4/6/8 for 128/192/256-bit keys
  var $Nr = $Nk + 6;       // no of rounds: 10/12/14 for 128/192/256-bit keys

   $w = new Array($Nb*($Nr+1));
   $temp = new Array(4);

  for ( $i=0; $i<$Nk; $i++) {
     $r = array($key[4*$i], $key[4*$i+1], $key[4*$i+2], $key[4*$i+3]);
    $w[$i] = $r;
  }

  for ( $i=$Nk; $i<($Nb*($Nr+1)); $i++) {
    $w[$i] = new Array(4);
    for ( $t=0; $t<4; $t++) $temp[$t] = $w[$i-1][$t];
    if ($i % $Nk == 0) {
      $temp = $Aes['subWord']($Aes['rotWord']($temp));
      for ( $t=0; $t<4; $t++) $temp[$t] ^= $Aes['rCon'][$i/$Nk][$t];
    } else if ($Nk > 6 && $i%$Nk == 4) {
      $temp = $Aes['subWord']($temp);
    }
    for ( $t=0; $t<4; $t++) $w[$i][$t] = $w[$i-$Nk][$t] ^ $temp[$t];
  }

  return $w;
}

/*
 * ---- remaining routines are private, not called externally ----
 */
 
$Aes['subBytes'] = function($s, $Nb) {    // apply SBox to state S [§5.1.1]
  for ( $r=0; $r<4; $r++) {
    for ( $c=0; $c<$Nb; $c++) $s[$r][$c] = $Aes['sBox'][$s[$r][$c]];
  }
  return $s;
}

$Aes['shiftRows'] = function($s, $Nb) {    // shift row r of state S left by r bytes [§5.1.2]
   $t = new Array(4);
  for ( $r=1; $r<4; $r++) {
    for ( $c=0; $c<4; $c++) $t[$c] = $s[$r][($c+$r)%$Nb];  // shift into temp copy
    for ( $c=0; $c<4; $c++) $s[$r][$c] = $t[$c];         // and copy back
  }          // note that this will work for Nb=4,5,6, but not 7,8 (always 4 for AES):
  return $s;  // see asmaes.sourceforge.net/rijndael/rijndaelImplementation.pdf
}

$Aes['mixColumns'] = function($s, $Nb) {   // combine bytes of each col of state S [§5.1.3]
  for ( $c=0; $c<4; $c++) {
     $a = new Array(4);  // 'a' is a copy of the current column from 's'
     $b = new Array(4);  // 'b' is a•{02} in GF(2^8)
    for ( $i=0; $i<4; $i++) {
      $a[$i] = $s[$i][$c];
      $b[$i] = $s[$i][$c]&0$x80 ? $s[$i][$c]<<1 ^ 0$x011b : $s[$i][$c]<<1;

    }
    // a[n] ^ b[n] is a•{03} in GF(2^8)
    $s[0][$c] = $b[0] ^ $a[1] ^ $b[1] ^ $a[2] ^ $a[3]; // 2*a0 + 3*a1 + a2 + a3
    $s[1][$c] = $a[0] ^ $b[1] ^ $a[2] ^ $b[2] ^ $a[3]; // a0 * 2*a1 + 3*a2 + a3
    $s[2][$c] = $a[0] ^ $a[1] ^ $b[2] ^ $a[3] ^ $b[3]; // a0 + a1 + 2*a2 + 3*a3
    $s[3][$c] = $a[0] ^ $b[0] ^ $a[1] ^ $a[2] ^ $b[3]; // 3*a0 + a1 + a2 + 2*a3
  }
  return $s;
}

$Aes['addRoundKey'] = function($state, $w, $rnd, $Nb) {  // xor Round Key into state S [§5.1.4]
  for ( $r=0; $r<4; $r++) {
    for ( $c=0; $c<$Nb; $c++) $state[$r][$c] ^= $w[$rnd*4+$c][$r];
  }
  return $state;
}

$Aes['subWord'] = function($w) {    // apply SBox to 4-byte word w
  for ( $i=0; $i<4; $i++) $w[$i] = $Aes['sBox'][$w[$i]];
  return $w;
}

$Aes['rotWord'] = function($w) {    // rotate 4-byte word w left by one byte
   $tmp = $w[0];
  for ( $i=0; $i<3; $i++) $w[$i] = $w[$i+1];
  $w[3] = $tmp;
  return $w;
}

// sBox is pre-computed multiplicative inverse in GF(2^8) used in subBytes and keyExpansion [§5.1.1]
$Aes['sBox'] =  array(0$x63,0$x7c,0$x77,0$x7b,0$xf2,0$x6b,0$x6f,0$xc5,0$x30,0$x01,0$x67,0$x2b,0$xfe,0$xd7,0$xab,0$x76,
             0$xca,0$x82,0$xc9,0$x7d,0$xfa,0$x59,0$x47,0$xf0,0$xad,0$xd4,0$xa2,0$xaf,0$x9c,0$xa4,0$x72,0$xc0,
             0$xb7,0$xfd,0$x93,0$x26,0$x36,0$x3f,0$xf7,0$xcc,0$x34,0$xa5,0$xe5,0$xf1,0$x71,0$xd8,0$x31,0$x15,
             0$x04,0$xc7,0$x23,0$xc3,0$x18,0$x96,0$x05,0$x9a,0$x07,0$x12,0$x80,0$xe2,0$xeb,0$x27,0$xb2,0$x75,
             0$x09,0$x83,0$x2c,0$x1a,0$x1b,0$x6e,0$x5a,0$xa0,0$x52,0$x3b,0$xd6,0$xb3,0$x29,0$xe3,0$x2f,0$x84,
             0$x53,0$xd1,0$x00,0$xed,0$x20,0$xfc,0$xb1,0$x5b,0$x6a,0$xcb,0$xbe,0$x39,0$x4a,0$x4c,0$x58,0$xcf,
             0$xd0,0$xef,0$xaa,0$xfb,0$x43,0$x4d,0$x33,0$x85,0$x45,0$xf9,0$x02,0$x7f,0$x50,0$x3c,0$x9f,0$xa8,
             0$x51,0$xa3,0$x40,0$x8f,0$x92,0$x9d,0$x38,0$xf5,0$xbc,0$xb6,0$xda,0$x21,0$x10,0$xff,0$xf3,0$xd2,
             0$xcd,0$x0c,0$x13,0$xec,0$x5f,0$x97,0$x44,0$x17,0$xc4,0$xa7,0$x7e,0$x3d,0$x64,0$x5d,0$x19,0$x73,
             0$x60,0$x81,0$x4f,0$xdc,0$x22,0$x2a,0$x90,0$x88,0$x46,0$xee,0$xb8,0$x14,0$xde,0$x5e,0$x0b,0$xdb,
             0$xe0,0$x32,0$x3a,0$x0a,0$x49,0$x06,0$x24,0$x5c,0$xc2,0$xd3,0$xac,0$x62,0$x91,0$x95,0$xe4,0$x79,
             0$xe7,0$xc8,0$x37,0$x6d,0$x8d,0$xd5,0$x4e,0$xa9,0$x6c,0$x56,0$xf4,0$xea,0$x65,0$x7a,0$xae,0$x08,
             0$xba,0$x78,0$x25,0$x2e,0$x1c,0$xa6,0$xb4,0$xc6,0$xe8,0$xdd,0$x74,0$x1f,0$x4b,0$xbd,0$x8b,0$x8a,
             0$x70,0$x3e,0$xb5,0$x66,0$x48,0$x03,0$xf6,0$x0e,0$x61,0$x35,0$x57,0$xb9,0$x86,0$xc1,0$x1d,0$x9e,
             0$xe1,0$xf8,0$x98,0$x11,0$x69,0$xd9,0$x8e,0$x94,0$x9b,0$x1e,0$x87,0$xe9,0$xce,0$x55,0$x28,0$xdf,
             0$x8c,0$xa1,0$x89,0$x0d,0$xbf,0$xe6,0$x42,0$x68,0$x41,0$x99,0$x2d,0$x0f,0$xb0,0$x54,0$xbb,0$x16);

// rCon is Round Constant used for the Key Expansion [1st col is 2^(r-1) in GF(2^8)] [§5.2]
$Aes['rCon'] = array( array(0$x00, 0$x00, 0$x00, 0$x00),
             array(0$x01, 0$x00, 0$x00, 0$x00),
             array(0$x02, 0$x00, 0$x00, 0$x00),
             array(0$x04, 0$x00, 0$x00, 0$x00),
             array(0$x08, 0$x00, 0$x00, 0$x00),
             array(0$x10, 0$x00, 0$x00, 0$x00),
             array(0$x20, 0$x00, 0$x00, 0$x00),
             array(0$x40, 0$x00, 0$x00, 0$x00),
             array(0$x80, 0$x00, 0$x00, 0$x00),
             array(0$x1b, 0$x00, 0$x00, 0$x00),
             array(0$x36, 0$x00, 0$x00, 0$x00) ); 


/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*  AES Counter-mode implementation in JavaScript (c) Chris Veness 2005-2011                      */
/*   - see http://csrc.nist.gov/publications/nistpubs/800-38a/sp800-38a.pdf                       */
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

$Aes['Ctr'] = array();  // Aes.Ctr namespace: a subclass or extension of Aes

/** 
 * Encrypt a text using AES encryption in Counter mode of operation
 *
 * Unicode multi-byte character safe
 *
 * @param {String} plaintext Source text to be encrypted
 * @param {String} password  The password to use to generate a key
 * @param {Number} nBits     Number of bits to be used in the key (128, 192, or 256)
 * @returns {string}         Encrypted text
 */
$Aes['Ctr']['encrypt'] = function($plaintext, $password, $nBits) {
   $blockSize = 16;  // block size fixed at 16 bytes / 128 bits (Nb=4) for AES
  if (!($nBits==128 || $nBits==192 || $nBits==256)) return '';  // standard allows 128/192/256 bit keys
  $plaintext = $Utf8['encode']($plaintext);
  $password = $Utf8['encode']($password);
  //var t = new Date();  // timer
        
  // use AES itself to encrypt password to get cipher key (using plain password as source for key 
  // expansion) - gives us well encrypted key (though hashed key might be preferred for prod'n use)
   $nBytes = $nBits/8;  // no bytes in key (16/24/32)
   $pwBytes = new Array($nBytes);
  for ( $i=0; $i<$nBytes; $i++) {  // use 1st 16/24/32 chars of password for key
    $pwBytes[$i] = isNaN($password['charCodeAt']($i)) ? 0 : $password['charCodeAt']($i);
  }
   $key = $Aes['cipher']($pwBytes, $Aes['keyExpansion']($pwBytes));  // gives us 16-byte key
  $key = $key['concat']($key['slice'](0, $nBytes-16));  // expand key to 16/24/32 bytes long

  // initialise 1st 8 bytes of counter block with nonce (NIST SP800-38A §B.2): [0-1] = millisec, 
  // [2-3] = random, [4-7] = seconds, together giving full sub-millisec uniqueness up to Feb 2106
   $counterBlock = new Array($blockSize);
  
   $nonce = (new Date())['getTime']();  // timestamp: milliseconds since 1-Jan-1970
   $nonceMs = $nonce%1000;
   $nonceSec = Math['floor']($nonce/1000);
   $nonceRnd = Math['floor'](Math['random']()*0$xffff);
  
  for ( $i=0; $i<2; $i++) $counterBlock[$i]   = ($nonceMs  >>> $i*8) & 0$xff;
  for ( $i=0; $i<2; $i++) $counterBlock[$i+2] = ($nonceRnd >>> $i*8) & 0$xff;
  for ( $i=0; $i<4; $i++) $counterBlock[$i+4] = ($nonceSec >>> $i*8) & 0$xff;
  
  // and convert it to a string to go on the front of the ciphertext
   $ctrTxt = '';
  for ( $i=0; $i<8; $i++) $ctrTxt += String['fromCharCode']($counterBlock[$i]);

  // generate key schedule - an expansion of the key into distinct Key Rounds for each round
   $keySchedule = $Aes['keyExpansion']($key);
  
   $blockCount = Math['ceil']($plaintext['length']/$blockSize);
   $ciphertxt = new Array($blockCount);  // ciphertext as array of strings
  
  for ( $b=0; $b<$blockCount; $b++) {
    // set counter (block #) in last 8 bytes of counter block (leaving nonce in 1st 8 bytes)
    // done in two stages for 32-bit ops: using two words allows us to go past 2^32 blocks (68GB)
    for ( $c=0; $c<4; $c++) $counterBlock[15-$c] = ($b >>> $c*8) & 0$xff;
    for ( $c=0; $c<4; $c++) $counterBlock[15-$c-4] = ($b/0$x100000000 >>> $c*8)

     $cipherCntr = $Aes['cipher']($counterBlock, $keySchedule);  // -- encrypt counter block --
    
    // block size is reduced on final block
     $blockLength = $b<$blockCount-1 ? $blockSize : ($plaintext['length']-1)%$blockSize+1;
     $cipherChar = new Array($blockLength);
    
    for ( $i=0; $i<$blockLength; $i++) {  // -- xor plaintext with ciphered counter char-by-char --
      $cipherChar[$i] = $cipherCntr[$i] ^ $plaintext['charCodeAt']($b*$blockSize+$i);
      $cipherChar[$i] = String['fromCharCode']($cipherChar[$i]);
    }
    $ciphertxt[$b] = $cipherChar['join'](''); 
  }

  // Array.join is more efficient than repeated string concatenation in IE
   $ciphertext = $ctrTxt + $ciphertxt['join']('');
  $ciphertext = $Base64['encode']($ciphertext);  // encode in base64
  
  //alert((new Date()) - t);
  return $ciphertext;
}

/** 
 * Decrypt a text encrypted by AES in counter mode of operation
 *
 * @param {String} ciphertext Source text to be encrypted
 * @param {String} password   The password to use to generate a key
 * @param {Number} nBits      Number of bits to be used in the key (128, 192, or 256)
 * @returns {String}          Decrypted text
 */
$Aes['Ctr']['decrypt'] = function($ciphertext, $password, $nBits) {
   $blockSize = 16;  // block size fixed at 16 bytes / 128 bits (Nb=4) for AES
  if (!($nBits==128 || $nBits==192 || $nBits==256)) return '';  // standard allows 128/192/256 bit keys
  $ciphertext = $Base64['decode']($ciphertext);
  $password = $Utf8['encode']($password);
  //var t = new Date();  // timer
  
  // use AES to encrypt password (mirroring encrypt routine)
   $nBytes = $nBits/8;  // no bytes in key
   $pwBytes = new Array($nBytes);
  for ( $i=0; $i<$nBytes; $i++) {
    $pwBytes[$i] = isNaN($password['charCodeAt']($i)) ? 0 : $password['charCodeAt']($i);
  }
   $key = $Aes['cipher']($pwBytes, $Aes['keyExpansion']($pwBytes));
  $key = $key['concat']($key['slice'](0, $nBytes-16));  // expand key to 16/24/32 bytes long

  // recover nonce from 1st 8 bytes of ciphertext
   $counterBlock = new Array(8);
  $ctrTxt = $ciphertext['slice'](0, 8);
  for ( $i=0; $i<8; $i++) $counterBlock[$i] = $ctrTxt['charCodeAt']($i);
  
  // generate key schedule
   $keySchedule = $Aes['keyExpansion']($key);

  // separate ciphertext into blocks (skipping past initial 8 bytes)
   $nBlocks = Math['ceil'](($ciphertext['length']-8) / $blockSize);
   $ct = new Array($nBlocks);
  for ( $b=0; $b<$nBlocks; $b++) $ct[$b] = $ciphertext['slice'](8+$b*$blockSize, 8+$b*$blockSize+$blockSize);
  $ciphertext = $ct;  // ciphertext is now array of block-length strings

  // plaintext will get generated block-by-block into array of block-length strings
   $plaintxt = new Array($ciphertext['length']);

  for ( $b=0; $b<$nBlocks; $b++) {
    // set counter (block #) in last 8 bytes of counter block (leaving nonce in 1st 8 bytes)
    for ( $c=0; $c<4; $c++) $counterBlock[15-$c] = (($b) >>> $c*8) & 0$xff;
    for ( $c=0; $c<4; $c++) $counterBlock[15-$c-4] = ((($b+1)/0$x100000000-1) >>> $c*8) & 0$xff;

     $cipherCntr = $Aes['cipher']($counterBlock, $keySchedule);  // encrypt counter block

     $plaintxtByte = new Array($ciphertext[$b]['length']);
    for ( $i=0; $i<$ciphertext[$b]['length']; $i++) {
      // -- xor plaintxt with ciphered counter byte-by-byte --
      $plaintxtByte[$i] = $cipherCntr[$i] ^ $ciphertext[$b]['charCodeAt']($i);
      $plaintxtByte[$i] = String['fromCharCode']($plaintxtByte[$i]);
    }
    $plaintxt[$b] = $plaintxtByte['join']('');
  }

  // join array of blocks into single plaintext string
   $plaintext = $plaintxt['join']('');
  $plaintext = $Utf8['decode']($plaintext);  // decode from UTF8 back to Unicode multi-byte chars
  
  //alert((new Date()) - t);
  return $plaintext;
}


/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*  Base64 class: Base 64 encoding / decoding (c) Chris Veness 2002-2011                          */
/*    note: depends on Utf8 class                                                                 */
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

 $Base64 = array();  // Base64 namespace

$Base64['code'] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

/**
 * Encode string into Base64, as defined by RFC 4648 [http://tools.ietf.org/html/rfc4648]
 * (instance method extending String object). As per RFC 4648, no newlines are added.
 *
 * @param {String} str The string to be encoded as base-64
 * @param {Boolean} [utf8encode=false] Flag to indicate whether str is Unicode string to be encoded 
 *   to UTF8 before conversion to base64; otherwise string is assumed to be 8-bit characters
 * @returns {String} Base64-encoded string
 */ 
$Base64['encode'] = function($str, $utf8encode) {  // http://tools.ietf.org/html/rfc4648
  $utf8encode =  (typeof $utf8encode == 'undefined') ? false : $utf8encode;
   $o1 = null; $o2 = null; $o3 = null; $bits = null; $h1 = null; $h2 = null; $h3 = null; $h4 = null; $e=array(); $pad = ''; $c; $plain; $coded;
   $b64 = $Base64['code'];
   
  $plain = $utf8encode ? $str['encodeUTF8']() : $str;
  
  $c = $plain['length'] % 3;  // pad string to length of multiple of 3
  if ($c > 0) { while ($c++ < 3) { $pad += '='; $plain += '\0'; } }
  // note: doing padding here saves us doing special-case packing for trailing 1 or 2 chars
   
  for ($c=0; $c<$plain['length']; $c+=3) {  // pack three octets into four hexets
    $o1 = $plain['charCodeAt']($c);
    $o2 = $plain['charCodeAt']($c+1);
    $o3 = $plain['charCodeAt']($c+2);
      
    $bits = $o1<<16 | $o2<<8 | $o3;
      
    $h1 = $bits>>18 & 0$x3f;
    $h2 = $bits>>12 & 0$x3f;
    $h3 = $bits>>6 & 0$x3f;
    $h4 = $bits & 0$x3f;

    // use hextets to index into code string
    $e[$c/3] = $b64['charAt']($h1) + $b64['charAt']($h2) + $b64['charAt']($h3) + $b64['charAt']($h4);
  }
  $coded = $e['join']('');  // join() is far faster than repeated string concatenation in IE
  
  // replace 'A's from padded nulls with '='s
  $coded = $coded['slice'](0, $coded['length']-$pad['length']) + $pad;
   
  return $coded;
}

/**
 * Decode string from Base64, as defined by RFC 4648 [http://tools.ietf.org/html/rfc4648]
 * (instance method extending String object). As per RFC 4648, newlines are not catered for.
 *
 * @param {String} str The string to be decoded from base-64
 * @param {Boolean} [utf8decode=false] Flag to indicate whether str is Unicode string to be decoded 
 *   from UTF8 after conversion from base64
 * @returns {String} decoded string
 */ 
$Base64['decode'] = function($str, $utf8decode) {
  $utf8decode =  (typeof $utf8decode == 'undefined') ? false : $utf8decode;
   $o1 = null; $o2 = null; $o3 = null; $h1 = null; $h2 = null; $h3 = null; $h4 = null; $bits = null; $d=array(); $plain; $coded;
   $b64 = $Base64['code'];

  $coded = $utf8decode ? $str['decodeUTF8']() : $str;
  
  
  for ( $c=0; $c<$coded['length']; $c+=4) {  // unpack four hexets into three octets
    $h1 = $b64['indexOf']($coded['charAt']($c));
    $h2 = $b64['indexOf']($coded['charAt']($c+1));
    $h3 = $b64['indexOf']($coded['charAt']($c+2));
    $h4 = $b64['indexOf']($coded['charAt']($c+3));
      
    $bits = $h1<<18 | $h2<<12 | $h3<<6 | $h4;
      
    $o1 = $bits>>>16 & 0$xff;
    $o2 = $bits>>>8 & 0$xff;
    $o3 = $bits & 0$xff;
    
    $d[$c/4] = String['fromCharCode']($o1, $o2, $o3);
    // check for padding
    if ($h4 == 0$x40) $d[$c/4] = String['fromCharCode']($o1, $o2);
    if ($h3 == 0$x40) $d[$c/4] = String['fromCharCode']($o1);
  }
  $plain = $d['join']('');  // join() is far faster than repeated string concatenation in IE
   
  return $utf8decode ? $plain['decodeUTF8']() : $plain; 
}


/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*  Utf8 class: encode / decode between multi-byte Unicode characters and UTF-8 multiple          */
/*              single-byte character encoding (c) Chris Veness 2002-2011                         */
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

 $Utf8 = array();  // Utf8 namespace

/**
 * Encode multi-byte Unicode string into utf-8 multiple single-byte characters 
 * (BMP / basic multilingual plane only)
 *
 * Chars in range U+0080 - U+07FF are encoded in 2 chars, U+0800 - U+FFFF in 3 chars
 *
 * @param {String} strUni Unicode string to be encoded as UTF-8
 * @returns {String} encoded string
 */
$Utf8['encode'] = function($strUni) {
  // use regular expressions & String.replace callback function for better efficiency 
  // than procedural approaches
   $strUtf = $strUni['replace'](
      /array(\$u0080-\$u07ff)/$g,  // U+0080 - U+07FF => 2 bytes 110yyyyy, 10zzzzzz
      function($c) { 
         $cc = $c['charCodeAt'](0);
        return String['fromCharCode'](0$xc0 | $cc>>6, 0$x80 | $cc&0$x3f); }
    );
  $strUtf = $strUtf['replace'](
      /array(\$u0800-\$uffff)/$g,  // U+0800 - U+FFFF => 3 bytes 1110xxxx, 10yyyyyy, 10zzzzzz
      function($c) { 
         $cc = $c['charCodeAt'](0); 
        return String['fromCharCode'](0$xe0 | $cc>>12, 0$x80 | $cc>>6&0$x3F, 0$x80 | $cc&0$x3f); }
    );
  return $strUtf;
}

/**
 * Decode utf-8 encoded string back into multi-byte Unicode characters
 *
 * @param {String} strUtf UTF-8 string to be decoded back to Unicode
 * @returns {String} decoded string
 */
$Utf8['decode'] = function($strUtf) {
  // note: decode 3-byte chars first as decoded 2-byte strings could appear to be 3-byte char!
   $strUni = $strUtf['replace'](
      /array(\$u00e0-\$u00ef)[\$u0080-\$u00bf][\$u0080-\$u00bf]/$g,  // 3-byte chars
      function($c) {  // (note parentheses for precence)
         $cc = (($c['charCodeAt'](0)&0$x0f)<<12) | (($c['charCodeAt'](1)&0$x3f)<<6) | ( $c['charCodeAt'](2)&0$x3f); 
        return String['fromCharCode']($cc); }
    );
  $strUni = $strUni['replace'](
      /array(\$u00c0-\$u00df)[\$u0080-\$u00bf]/$g,                 // 2-byte chars
      function($c) {  // (note parentheses for precence)
         $cc = ($c['charCodeAt'](0)&0$x1f)<<6 | $c['charCodeAt'](1)&0$x3f;
        return String['fromCharCode']($cc); }
    );
  return $strUni;
}
