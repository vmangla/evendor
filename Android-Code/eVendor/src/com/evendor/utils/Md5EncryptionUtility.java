package com.evendor.utils;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;


public class Md5EncryptionUtility {

	/* Encryption Method */ 
	public static String encrypt(String message)
	{
		MessageDigest md = null;
		try {
			md = MessageDigest.getInstance("MD5");
		} catch (NoSuchAlgorithmException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		md.update(message.getBytes());

		byte byteData[] = md.digest();

		StringBuffer sb = new StringBuffer();
		for (int i = 0; i < byteData.length; i++) {
			sb.append(Integer.toString((byteData[i] & 0xff) + 0x100, 16).substring(1));
		}

		//System.out.println("Digest(in hex format):: " + sb.toString());

		return sb.toString();

	}

}





/*

	private static final String ALGORITHM = "md5";
	private static final String DIGEST_STRING = "HG58YZ3CR9";
	private static final String CHARSET_UTF_8 = "utf-8";
	private static final String SECRET_KEY_ALGORITHM = "DESede";
	private static final String TRANSFORMATION_PADDING = "DESede/CBC/PKCS5Padding";

	 Encryption Method 
	public static String encrypt(String message)
	{
		MessageDigest md = null;
		byte[] digestOfPassword = null;
		Cipher cipher = null;
		byte[] plainTextBytes = null;
		byte[] cipherText = null;

		try {
			md = MessageDigest.getInstance(ALGORITHM);
		} catch (NoSuchAlgorithmException e) {
			e.printStackTrace();
		}

		try {
			digestOfPassword = md.digest(DIGEST_STRING.getBytes(CHARSET_UTF_8));
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		}
		final byte[] keyBytes = Arrays.copyOf(digestOfPassword, 24);
		for (int j = 0, k = 16; j < 8;) {
			keyBytes[k++] = keyBytes[j++];
		}

		final SecretKey key = new SecretKeySpec(keyBytes, SECRET_KEY_ALGORITHM);
		final IvParameterSpec iv = new IvParameterSpec(new byte[8]);

		try {
			cipher = Cipher.getInstance(TRANSFORMATION_PADDING);
		} catch (NoSuchAlgorithmException e) {
			e.printStackTrace();
		} catch (NoSuchPaddingException e) {
			e.printStackTrace();
		}
		try {
			cipher.init(Cipher.ENCRYPT_MODE, key, iv);
		} catch (InvalidKeyException e) {
			e.printStackTrace();
		} catch (InvalidAlgorithmParameterException e) {
			e.printStackTrace();
		}


		try {
			plainTextBytes = message.getBytes(CHARSET_UTF_8);
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		}

		try {
			cipherText = cipher.doFinal(plainTextBytes);
		} catch (IllegalBlockSizeException e) {
			e.printStackTrace();
		} catch (BadPaddingException e) {
			e.printStackTrace();
		}

		return new String(cipherText);
	} 


 */

