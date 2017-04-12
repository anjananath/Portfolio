using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Security.Cryptography;
using System.Text;
using System.Threading.Tasks;

namespace PtaLogin
{
    class Program
    {
        static void Main(string[] args)
        {
            string password = "reliance86452937";

            string ptaDataString = "";

            ICollection<KeyValuePair<String, String>> parms = new Dictionary<String, String>();

   

            Encoding p_li = new System.Text.UTF8Encoding();

 

                parms.Add(new KeyValuePair<String, String>("p_userid", "anjana"));

                

                 foreach (KeyValuePair<string, string> pair in parms)

                {

                    if (ptaDataString.Length > 0)

                        ptaDataString += "&";

                    ptaDataString += String.Format("{0}={1}", pair.Key, pair.Value);

                }

 

            byte[] plainText = p_li.GetBytes(ptaDataString);

             byte[] cipherText = AES_CBC_128_Encrypt(plainText, password);  

// password is the p_li_password config value

      

            string new_pli = Convert.ToBase64String(cipherText);

             new_pli = new_pli.Replace("+", "_");

            new_pli = new_pli.Replace("/", "~");

            new_pli = new_pli.Replace("=", "!");

            string url = "http://reliancecommercialfinance--tst.custhelp.com/ci/pta/login/redirect_to/home/lan/RLHLAHM000067053/p_li/" + new_pli;
             System.Diagnostics.Process.Start(url);

         
        }

        private static byte[] AES_CBC_128_Encrypt(byte[] plainText, string password)
        {
            Encoding stringEncoding = new System.Text.UTF8Encoding();

            byte[] salt = new byte[8];

            new RNGCryptoServiceProvider().GetBytes(salt);



            AesCryptoServiceProvider aes = new AesCryptoServiceProvider();

            aes.Mode = CipherMode.CBC;

            aes.KeySize = 128;

            aes.Key = stringEncoding.GetBytes(password);

            aes.IV = new byte[aes.IV.Length];

            // aes.Padding is left default PaddingMode.PKCS7



            // Overestimate encrypted size requirements

            byte[] encryptedDataBuffer = new byte[plainText.Length + 32 + 32 + 8];

            MemoryStream encryptedOutput = new MemoryStream(encryptedDataBuffer, true);

            CryptoStream encStream = new CryptoStream(encryptedOutput, aes.CreateEncryptor(),

                                                      CryptoStreamMode.Write);



            encStream.Write(plainText, 0, plainText.Length);



            encStream.FlushFinalBlock();

            byte[] encryptedData = new byte[encryptedOutput.Position];

            Array.Copy(encryptedDataBuffer, encryptedData, encryptedData.Length);

            encStream.Close();

            Console.WriteLine(encryptedData);

            return encryptedData;
        }
    }
}
